<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class SubscriptionTransformedEvent extends Event implements ShopwareSalesChannelEvent
{
    /**
     * @param array<string, mixed> $subscriptionData
     */
    public function __construct(
        private readonly array $subscriptionData,
        private readonly SalesChannelContext $salesChannelContext,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSubscriptionData(): array
    {
        return $this->subscriptionData;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }
}
