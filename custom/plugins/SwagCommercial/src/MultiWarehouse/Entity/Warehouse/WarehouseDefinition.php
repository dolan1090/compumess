<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Warehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse\OrderProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse\WarehouseGroupWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'warehouse';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WarehouseEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WarehouseCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            new LongTextField('description', 'description'),
            (new OneToManyAssociationField('productWarehouses', ProductWarehouseDefinition::class, 'warehouse_id'))->addFlags(new CascadeDelete()),
            new ManyToManyAssociationField(
                'groups',
                WarehouseGroupDefinition::class,
                WarehouseGroupWarehouseDefinition::class,
                'warehouse_id',
                'warehouse_group_id'
            ),
            (new OneToManyAssociationField(
                'orderProducts',
                OrderProductWarehouseDefinition::class,
                'warehouse_id'
            ))->addFlags(new CascadeDelete()),
        ]);
    }
}
