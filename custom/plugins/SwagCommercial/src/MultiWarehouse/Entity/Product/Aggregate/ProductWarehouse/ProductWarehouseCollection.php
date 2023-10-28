<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductWarehouseEntity>
 */
#[Package('inventory')]
class ProductWarehouseCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_warehouse_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductWarehouseEntity::class;
    }
}
