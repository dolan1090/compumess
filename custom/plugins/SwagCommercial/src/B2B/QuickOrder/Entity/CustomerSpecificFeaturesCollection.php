<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @extends EntityCollection<CustomerSpecificFeaturesEntity>
 */
#[Package('checkout')]
class CustomerSpecificFeaturesCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'customer_specific_features_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomerSpecificFeaturesEntity::class;
    }
}
