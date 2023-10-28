<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WarehouseGroupWarehouseEntity>
 */
#[Package('inventory')]
class WarehouseGroupWarehouseCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'warehouse_group_warehouse_collection';
    }

    protected function getExpectedClass(): string
    {
        return WarehouseGroupWarehouseEntity::class;
    }
}
