<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Extension\Product;

use Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse\OrderProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup\ProductWarehouseGroupDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class ProductExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'warehouseGroups',
                WarehouseGroupDefinition::class,
                ProductWarehouseGroupDefinition::class,
                'product_id',
                'warehouse_group_id'
            ))->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'warehouses',
                ProductWarehouseDefinition::class,
                'product_id'
            ))->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'orderWarehouses',
                OrderProductWarehouseDefinition::class,
                'product_id'
            ))->addFlags(new CascadeDelete())
        );
    }
}
