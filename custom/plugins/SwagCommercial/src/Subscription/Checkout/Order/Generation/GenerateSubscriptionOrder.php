<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('checkout')]
class GenerateSubscriptionOrder implements AsyncMessageInterface
{
    public function __construct(private readonly string $subscriptionId)
    {
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }
}
