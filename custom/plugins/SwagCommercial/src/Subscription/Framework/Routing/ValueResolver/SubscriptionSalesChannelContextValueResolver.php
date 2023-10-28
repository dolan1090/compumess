<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing\ValueResolver;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Routing\SubscriptionRequest;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[Package('checkout')]
class SubscriptionSalesChannelContextValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if ($argument->getType() !== SalesChannelContext::class) {
            return;
        }

        if (!$request->attributes->getBoolean(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CONTEXT)) {
            return;
        }

        yield $request->attributes->get(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
