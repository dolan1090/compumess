<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionEvents;
use Shopware\Commercial\Subscription\Framework\Routing\Mapping\SubscriptionCartMappingResolver;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Checkout\Cart\Order\Transformer\CustomerTransformer;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextTokenChangeEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionCartSubscriber implements EventSubscriberInterface
{
    public const CART_SOURCE_SUBSCRIPTION = 'subscription';

    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartPersister $cartPersister,
        private readonly SubscriptionCartMappingResolver $subscriptionCartResolver,
        private readonly IntervalCalculator $intervalCalculator,
        private readonly RequestStack $requestStack,
        private readonly SalesChannelContextPersister $salesChannelContextPersister,
        private readonly SubscriptionTransformer $subscriptionTransformer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SubscriptionEvents::SUBSCRIPTION_CART_SAVED => [
                ['persistMapping'],
            ],
            SubscriptionEvents::SUBSCRIPTION_CART_VERIFY_PERSIST => [
                ['setAlwaysPersist'],
            ],
            /**
             * @phpstan-ignore-next-line event is deprecated because interface will be removed tag:v6.6.0
             */
            CustomerLoginEvent::class => [
                ['propagateLogin', 1000],
            ],
            SalesChannelContextTokenChangeEvent::class => [
                ['propagateTokenChange', 1000],
            ],
            SubscriptionEvents::SUBSCRIPTION_CART_CONVERTED => [
                ['createSubscription', 5000],
            ],
        ];
    }

    public function setAlwaysPersist(CartVerifyPersistEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $event->setShouldPersist(true);
    }

    public function persistMapping(CartSavedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $this->subscriptionCartResolver->persistMapping($event->getSalesChannelContext());
    }

    /**
     * @phpstan-ignore-next-line event is deprecated because interface will be removed tag:v6.6.0
     */
    public function propagateLogin(CustomerLoginEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $oldContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$oldContext instanceof SalesChannelContext) {
            return;
        }

        $oldToken = $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);

        if (!\is_string($oldToken)) {
            return;
        }

        $subscriptionCarts = $this->subscriptionCartResolver->getSubscriptionCarts($oldToken);

        // replace subscription tokens with the new main context token
        foreach ($subscriptionCarts as $subscriptionCart) {
            $token = $this->subscriptionCartResolver->getSubscriptionToken(
                $subscriptionCart['intervalId'],
                $subscriptionCart['planId'],
                $event->getContextToken()
            );

            if ($token) {
                // we do not merge guest carts, we simply delete the existing ones and contexts
                $this->cartPersister->delete($token, $oldContext);
                $this->salesChannelContextPersister->delete($token, $oldContext->getSalesChannelId(), $oldContext->getCustomerId());
                $this->subscriptionCartResolver->deleteSubscriptionCart($token);
            }

            $this->subscriptionCartResolver->replaceSubscriptionToken(
                $subscriptionCart['subscriptionToken'],
                $event->getContextToken()
            );
        }
    }

    public function propagateTokenChange(SalesChannelContextTokenChangeEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $subscriptionCarts = $this->subscriptionCartResolver->getSubscriptionCarts($event->getPreviousToken());
        foreach ($subscriptionCarts as $subscriptionCart) {
            $this->subscriptionCartResolver->replaceSubscriptionToken(
                $subscriptionCart['subscriptionToken'],
                $event->getCurrentToken(),
            );
        }
    }

    public function createSubscription(CartConvertedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$event->getSalesChannelContext()->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return;
        }

        /** @var SubscriptionContextStruct $subscriptionExtension */
        $subscriptionExtension = $event->getSalesChannelContext()->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        $convertedCart = $event->getConvertedCart();

        if (\array_key_exists('orderDateTime', $convertedCart) && \is_string($convertedCart['orderDateTime'])) {
            $convertedCart['orderDateTime'] = $this->intervalCalculator->getInitialRunDate(
                $subscriptionExtension->getInterval(),
                new \DateTime($convertedCart['orderDateTime'])
            )->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        }

        $subscriptionData = $this->subscriptionTransformer->transform($convertedCart, $event->getSalesChannelContext());
        $customer = $event->getSalesChannelContext()->getCustomer();

        if (!$customer) {
            throw CartException::customerNotLoggedIn();
        }

        $subscriptionData['subscriptionCustomer'] = CustomerTransformer::transform($customer);
        $convertedCart['subscription'] = $subscriptionData;

        $event->setConvertedCart($convertedCart);
    }
}
