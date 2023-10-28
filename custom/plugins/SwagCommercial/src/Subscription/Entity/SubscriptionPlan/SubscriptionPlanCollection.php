<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionPlanEntity>
 */
#[Package('checkout')]
class SubscriptionPlanCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'subscription_plan_collection';
    }

    public function filterAvailablePlans(Context $context): self
    {
        return $this->filter(static function (SubscriptionPlanEntity $plan) use ($context): bool {
            if (!$plan->isActive()) {
                return false;
            }

            if (!$plan->getSubscriptionIntervals()) {
                return false;
            }

            if ($plan->getAvailabilityRuleId() && !\in_array($plan->getAvailabilityRuleId(), $context->getRuleIds(), true)) {
                return false;
            }

            $plan->setSubscriptionIntervals($plan->getSubscriptionIntervals()->filterAvailableIntervals($context));

            if (!$plan->getSubscriptionIntervals()?->count()) {
                return false;
            }

            return true;
        });
    }

    public function getPlanWithHighestDiscount(): ?SubscriptionPlanEntity
    {
        /** @var SubscriptionPlanEntity|null $result */
        $result = $this->reduce(fn ($highestDiscount, $plan) => $highestDiscount->getDiscountPercentage() < $plan->getDiscountPercentage() ? $plan : $highestDiscount, $this->first());

        return $result;
    }

    protected function getExpectedClass(): string
    {
        return SubscriptionPlanEntity::class;
    }
}
