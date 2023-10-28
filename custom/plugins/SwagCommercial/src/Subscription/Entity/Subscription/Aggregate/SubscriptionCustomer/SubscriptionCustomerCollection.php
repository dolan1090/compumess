<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionCustomer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SubscriptionCustomerEntity>
 */
#[Package('checkout')]
class SubscriptionCustomerCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'subscription_customer_collection';
    }

    protected function getExpectedClass(): string
    {
        return SubscriptionCustomerEntity::class;
    }
}
