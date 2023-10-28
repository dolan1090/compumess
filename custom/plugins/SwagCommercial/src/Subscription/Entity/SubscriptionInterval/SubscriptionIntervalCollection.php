<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionIntervalEntity>
 */
#[Package('checkout')]
class SubscriptionIntervalCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'subscription_interval_collection';
    }

    public function filterAvailableIntervals(Context $context): self
    {
        return $this->filter(static function (SubscriptionIntervalEntity $interval) use ($context): bool {
            if (!$interval->isActive()) {
                return false;
            }

            if (!$interval->getAvailabilityRuleId()) {
                return true;
            }

            return \in_array($interval->getAvailabilityRuleId(), $context->getRuleIds(), true);
        });
    }

    protected function getExpectedClass(): string
    {
        return SubscriptionIntervalEntity::class;
    }
}
