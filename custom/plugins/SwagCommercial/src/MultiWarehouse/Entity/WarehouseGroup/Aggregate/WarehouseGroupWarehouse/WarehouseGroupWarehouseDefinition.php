<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseGroupWarehouseDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'warehouse_group_warehouse';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WarehouseGroupWarehouseEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WarehouseGroupWarehouseCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('warehouse_id', 'warehouseId', WarehouseDefinition::class))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('warehouse_group_id', 'warehouseGroupId', WarehouseGroupDefinition::class))->addFlags(new Required(), new PrimaryKey()),
            new IntField('priority', 'priority'),
            new ManyToOneAssociationField('warehouseGroup', 'warehouse_group_id', WarehouseGroupDefinition::class, 'id'),
            new ManyToOneAssociationField('warehouse', 'warehouse_id', WarehouseDefinition::class, 'id'),
        ]);
    }
}
