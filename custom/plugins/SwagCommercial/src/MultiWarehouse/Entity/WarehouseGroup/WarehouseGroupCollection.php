<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WarehouseGroupEntity>
 */
#[Package('inventory')]
class WarehouseGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'warehouse_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return WarehouseGroupEntity::class;
    }
}
