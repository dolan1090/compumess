<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductWarehouseGroupDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'product_warehouse_group';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductWarehouseGroupCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required(), new PrimaryKey()),
            (new ReferenceVersionField(ProductDefinition::class, 'product_version_id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('warehouse_group_id', 'warehouseGroupId', WarehouseGroupDefinition::class))->addFlags(new Required(), new PrimaryKey()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id'),
            new ManyToOneAssociationField('warehouseGroup', 'warehouse_group_id', WarehouseGroupDefinition::class, 'id'),
        ]);
    }
}
