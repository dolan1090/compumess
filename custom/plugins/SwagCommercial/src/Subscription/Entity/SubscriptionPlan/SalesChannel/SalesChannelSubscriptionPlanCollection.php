<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SalesChannel;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SalesChannelSubscriptionPlanCollection extends SubscriptionPlanCollection
{
    protected function getExpectedClass(): string
    {
        return SalesChannelSubscriptionPlanEntity::class;
    }
}
