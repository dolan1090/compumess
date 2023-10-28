<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderWarehouseGroup;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderWarehouseGroupEntity>
 */
#[Package('inventory')]
class OrderWarehouseGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_warehouse_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderWarehouseGroupEntity::class;
    }
}
