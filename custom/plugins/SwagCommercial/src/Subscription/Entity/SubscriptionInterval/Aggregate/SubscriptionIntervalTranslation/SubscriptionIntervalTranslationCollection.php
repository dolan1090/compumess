<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionIntervalTranslationEntity>
 */
#[Package('checkout')]
class SubscriptionIntervalTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SubscriptionIntervalTranslationEntity::class;
    }
}
