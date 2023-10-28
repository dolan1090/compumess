<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionAddressCollection extends CustomerAddressCollection
{
    public function getApiAlias(): string
    {
        return 'subscription_address_collection';
    }

    protected function getExpectedClass(): string
    {
        return SubscriptionAddressEntity::class;
    }
}
