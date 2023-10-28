<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse;

use Shopware\Core\Framework\Event\Annotation\Event;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class MultiWarehouseEvents
{
    /**
     * @Event("Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent")
     */
    final public const PRODUCT_WAREHOUSE_WRITTEN_EVENT = 'product_warehouse.written';

    /**
     * @Event("Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent")
     */
    final public const PRODUCT_WAREHOUSE_GROUP_WRITTEN_EVENT = 'product_warehouse_group.written';

    /**
     * @Event("Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent")
     */
    final public const WAREHOUSE_GROUP_WAREHOUSE_WRITTEN_EVENT = 'warehouse_group_warehouse.written';
}
