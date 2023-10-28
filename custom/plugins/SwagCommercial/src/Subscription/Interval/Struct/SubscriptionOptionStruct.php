<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Interval\Struct;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('checkout')]
class SubscriptionOptionStruct extends Struct
{
    /**
     * @internal
     */
    public function __construct(
        protected SubscriptionPlanEntity $plan,
        protected SubscriptionIntervalEntity $interval
    ) {
    }

    public function getPlan(): SubscriptionPlanEntity
    {
        return $this->plan;
    }

    public function getInterval(): SubscriptionIntervalEntity
    {
        return $this->interval;
    }

    /**
     * @return array{planId: string, intervalId: string}
     */
    public function getPayload(): array
    {
        return [
            'planId' => $this->plan->getId(),
            'intervalId' => $this->interval->getId(),
        ];
    }
}
