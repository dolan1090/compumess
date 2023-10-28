<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionEntity>
 */
#[Package('checkout')]
class SubscriptionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'subscription_collection';
    }

    protected function getExpectedClass(): string
    {
        return SubscriptionEntity::class;
    }
}
