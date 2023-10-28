<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Warehouse;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<WarehouseEntity>
 */
#[Package('inventory')]
class WarehouseCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'warehouse_collection';
    }

    protected function getExpectedClass(): string
    {
        return WarehouseEntity::class;
    }
}
