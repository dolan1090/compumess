<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionEventDispatcher;
use Shopware\Commercial\Subscription\Framework\Routing\Mapping\SubscriptionContextStructBuilder;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\ApiOrderCartService;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\Framework\Routing\RequestContextResolverInterface;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('core')]
class SalesChannelRequestContextResolver implements RequestContextResolverInterface
{
    public const SUBSCRIPTION_PERMISSION = 'subscription';

    /**
     * @internal
     */
    public function __construct(
        private readonly ApiOrderCartService $apiOrderCartService,
        private readonly RequestContextResolverInterface $decorated,
        private readonly SalesChannelContextServiceInterface $contextService,
        private readonly SubscriptionContextStructBuilder $contextStructBuilder,
        private readonly SubscriptionEventDispatcher $eventDispatcher,
    ) {
    }

    public function resolve(Request $request): void
    {
        $this->decorated->resolve($request);

        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$request->attributes->getBoolean(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CONTEXT)) {
            return;
        }

        $mainContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$mainContext instanceof SalesChannelContext) {
            throw RoutingException::invalidRequestParameter(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        }

        $contextStruct = $this->contextStructBuilder->buildFromRequest($request, $mainContext->getContext());
        $contextToken = $contextStruct->getSubscriptionToken();
        $salesChannelId = $mainContext->getSalesChannelId();

        $this->apiOrderCartService->addPermission($contextToken, self::SUBSCRIPTION_PERMISSION, $salesChannelId);

        $context = $this->contextService->get(
            new SalesChannelContextServiceParameters(
                $salesChannelId,
                $contextToken,
                $mainContext->getLanguageId(),
                $mainContext->getCurrencyId(),
                $mainContext->getDomainId(),
                $mainContext->getContext(),
                $mainContext->getCustomerId(),
            )
        );

        $context->addExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION, $contextStruct);

        $request->attributes->set(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_CONTEXT_TOKEN, $contextToken);
        $request->attributes->set(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_CONTEXT_OBJECT, $context->getContext());
        $request->attributes->set(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT, $context);

        $this->eventDispatcher->dispatch(new SalesChannelContextResolvedEvent($context, $contextToken));
    }
}
