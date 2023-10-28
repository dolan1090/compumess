<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class SubscriptionRequest
{
    public const HEADER_SUBSCRIPTION_PLAN = 'sw-subscription-plan';
    public const HEADER_SUBSCRIPTION_INTERVAL = 'sw-subscription-interval';

    public const ATTRIBUTE_SUBSCRIPTION_CONTEXT_TOKEN = 'sw-subscription-context-token';

    public const ATTRIBUTE_SUBSCRIPTION_CONTEXT_OBJECT = 'sw-subscription-context';
    public const ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT = 'sw-subscription-sales-channel-context';

    public const ATTRIBUTE_IS_SUBSCRIPTION_CART = '_subscriptionCart';
    public const ATTRIBUTE_IS_SUBSCRIPTION_CONTEXT = '_subscriptionContext';

    /**
     * @codeCoverageIgnore private constructor
     */
    private function __construct()
    {
    }
}
