<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Struct;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('checkout')]
class SubscriptionContextStruct extends Struct
{
    public const SUBSCRIPTION_EXTENSION = 'subscription';

    public function __construct(
        protected string $mainToken,
        protected \DateTimeInterface $nextSchedule,
        protected SubscriptionIntervalEntity $interval,
        protected SubscriptionPlanEntity $plan,
        protected ?string $subscriptionToken = null
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

    public function getNextSchedule(): \DateTimeInterface
    {
        return $this->nextSchedule;
    }

    public function getMainToken(): string
    {
        return $this->mainToken;
    }

    public function getSubscriptionToken(): string
    {
        if (!$this->subscriptionToken) {
            $this->subscriptionToken = \md5($this->mainToken . $this->interval->getId() . $this->plan->getId());
        }

        return $this->subscriptionToken;
    }
}
