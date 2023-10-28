<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Recurring;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Core\Checkout\Payment\Cart\Recurring\RecurringDataStruct;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionRecurringDataStruct extends RecurringDataStruct
{
    public function __construct(protected SubscriptionEntity $subscription)
    {
        parent::__construct($subscription->getId(), $subscription->getNextSchedule());
    }

    public function getSubscription(): SubscriptionEntity
    {
        return $this->subscription;
    }
}
