<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderProductWarehouseEntity>
 */
#[Package('inventory')]
class OrderProductWarehouseCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OrderProductWarehouseEntity::class;
    }
}
