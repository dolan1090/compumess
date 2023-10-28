<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\SubscriptionCartException;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionEvents;
use Shopware\Commercial\Subscription\Framework\Routing\SubscriptionRequest;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Event\StorefrontRedirectEvent;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
#[Package('checkout')]
class StorefrontSubscriber implements EventSubscriberInterface
{
    public const SUBSCRIPTION_TOKEN_ATTRIBUTE = 'subscriptionToken';
    public const SUBSCRIPTION_PLAN_OPTION_REQUEST_PARAMETER = 'subscription-plan-option';

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['propagateSubscriptionDetails', 35],
            ],
            KernelEvents::CONTROLLER_ARGUMENTS => [
                ['setSubscriptionToken'],
            ],
            SubscriptionEvents::SUBSCRIPTION_BEFORE_LINE_ITEM_ADDED => [
                ['preventMultipleProductLineItems'],
            ],
            StorefrontRedirectEvent::class => [
                ['redirectSubscriptionRequests'],
            ],
            StorefrontRenderEvent::class => [
                ['replaceTwigContext', 1000],
            ],
        ];
    }

    public function propagateSubscriptionDetails(ControllerEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$this->isSubscriptionStorefrontRequest($event->getRequest())) {
            return;
        }

        $planOption = $event->getRequest()->get(self::SUBSCRIPTION_PLAN_OPTION_REQUEST_PARAMETER);

        if (\is_string($planOption) && $planOption !== '') {
            $intervalKey = \sprintf(
                '%s-%s-%s',
                self::SUBSCRIPTION_PLAN_OPTION_REQUEST_PARAMETER,
                $planOption,
                'interval'
            );

            $intervalOption = $event->getRequest()->get($intervalKey);

            if (!$intervalOption || !\is_string($intervalOption)) {
                throw SubscriptionCartException::missingDataForConversion('intervalOption');
            }

            $this->propagateToMainRequest($intervalOption, $planOption);

            return;
        }

        $mainRequest = $this->requestStack->getMainRequest();
        $token = $event->getRequest()->attributes->getAlnum(self::SUBSCRIPTION_TOKEN_ATTRIBUTE);
        $resolved = false;

        if ($mainRequest && $token !== '' && \is_string($mainRequest->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN))) {
            $resolved = $this->resolveFromToken($token, $mainRequest->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN));
        }

        if (!$resolved) {
            $event->setController(fn (): RedirectResponse => new RedirectResponse($this->router->generate('frontend.checkout.cart.page')));
            $event->getRequest()->attributes->set(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CART, false);
            $event->getRequest()->attributes->set(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CONTEXT, false);
        }
    }

    /**
     * @internal this limitation may be removed in a future version
     */
    public function preventMultipleProductLineItems(BeforeLineItemAddedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $mainRequest = $this->requestStack->getMainRequest();
        if (!$mainRequest) {
            return;
        }

        if (!$this->isSubscriptionStorefrontRequest($mainRequest)) {
            return;
        }

        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            if ($lineItem->getId() === $event->getLineItem()->getId()) {
                $lineItem->setQuantity($event->getLineItem()->getQuantity());

                continue;
            }

            $event->getCart()->remove($lineItem->getId());
        }
    }

    public function redirectSubscriptionRequests(StorefrontRedirectEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        if (!$this->isSubscriptionStorefrontRequest($request)) {
            return;
        }

        if (\str_contains($event->getRoute(), '.subscription.')) {
            return;
        }

        $event->setRoute(\str_replace('frontend.', 'frontend.subscription.', $event->getRoute()));

        $parameters = $event->getParameters();
        $parameters[self::SUBSCRIPTION_TOKEN_ATTRIBUTE] = $request->attributes->getAlnum(self::SUBSCRIPTION_TOKEN_ATTRIBUTE);
        $event->setParameters($parameters);
    }

    public function replaceTwigContext(StorefrontRenderEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$this->isSubscriptionStorefrontRequest($event->getRequest())) {
            return;
        }

        $context = $event->getRequest()->get(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof SalesChannelContext) {
            return;
        }

        $event->setParameter('context', $context);
        $event->setSalesChannelContext($context);
    }

    public function setSubscriptionToken(ControllerArgumentsEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$this->isSubscriptionStorefrontRequest($event->getRequest())) {
            return;
        }

        $token = $event->getRequest()->attributes->getAlnum(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_CONTEXT_TOKEN);

        $parameters = $event->getRequest()->get('redirectParameters', []);
        if (!\is_array($parameters)) {
            return;
        }

        $parameters[self::SUBSCRIPTION_TOKEN_ATTRIBUTE] = $token;
        $event->getRequest()->attributes->set('redirectParameters', $parameters);
    }

    private function resolveFromToken(string $subscriptionToken, string $mainToken): bool
    {
        /** @var array{interval_id: string, plan_id: string}|false $result */
        $result = $this->connection->executeQuery(
            'SELECT LOWER(HEX(plan_id)) as plan_id, LOWER(HEX(interval_id)) as interval_id
                FROM subscription_cart
                WHERE `subscription_token` = :subscriptionToken AND `main_token` = :mainToken',
            [
                'subscriptionToken' => $subscriptionToken,
                'mainToken' => $mainToken,
            ]
        )->fetchAssociative();

        if (!$result) {
            return false;
        }

        $this->propagateToMainRequest($result['interval_id'], $result['plan_id']);

        return true;
    }

    private function isSubscriptionStorefrontRequest(Request $request): bool
    {
        if (!$request->attributes->getBoolean(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CART)) {
            return false;
        }

        /** @var list<string> $scopes */
        $scopes = $request->get(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, []);

        if (!\in_array(StorefrontRouteScope::ID, $scopes, true)) {
            return false;
        }

        return true;
    }

    private function propagateToMainRequest(string $intervalId, string $planId): void
    {
        $mainRequest = $this->requestStack->getMainRequest();
        if (!$mainRequest) {
            return;
        }

        $mainRequest->headers->set(SubscriptionRequest::HEADER_SUBSCRIPTION_PLAN, $planId);
        $mainRequest->headers->set(SubscriptionRequest::HEADER_SUBSCRIPTION_INTERVAL, $intervalId);
    }
}
