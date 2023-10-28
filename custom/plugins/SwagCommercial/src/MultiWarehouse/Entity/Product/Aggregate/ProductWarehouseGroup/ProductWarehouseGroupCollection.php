<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductWarehouseGroupEntity>
 */
#[Package('inventory')]
class ProductWarehouseGroupCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_warehouse_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductWarehouseGroupEntity::class;
    }
}
