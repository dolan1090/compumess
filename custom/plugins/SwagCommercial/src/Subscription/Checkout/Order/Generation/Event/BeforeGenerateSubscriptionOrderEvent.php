<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class BeforeGenerateSubscriptionOrderEvent extends Event implements ShopwareEvent
{
    /**
     * @internal
     *
     * @param array<string, mixed> $orderData
     * @param array<string, mixed> $subscriptionUpdate
     */
    public function __construct(
        private readonly array $orderData,
        private readonly array $subscriptionUpdate,
        private readonly Context $context
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderData(): array
    {
        return $this->orderData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSubscriptionUpdate(): array
    {
        return $this->subscriptionUpdate;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
