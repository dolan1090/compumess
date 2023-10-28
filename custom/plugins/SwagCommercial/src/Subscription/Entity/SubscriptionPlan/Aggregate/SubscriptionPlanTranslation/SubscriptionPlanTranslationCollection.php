<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionPlanTranslationEntity>
 */
#[Package('checkout')]
class SubscriptionPlanTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SubscriptionPlanTranslationEntity::class;
    }
}
