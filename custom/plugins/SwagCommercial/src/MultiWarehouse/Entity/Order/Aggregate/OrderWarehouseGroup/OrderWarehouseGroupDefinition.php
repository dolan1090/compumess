<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderWarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class OrderWarehouseGroupDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_warehouse_group';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return OrderWarehouseGroupEntity::class;
    }

    public function getCollectionClass(): string
    {
        return OrderWarehouseGroupCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class, 'order_version_id'))->addFlags(new Required()),

            (new FkField('warehouse_group_id', 'warehouseGroupId', WarehouseGroupDefinition::class))->addFlags(new Required()),

            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id'),
            new ManyToOneAssociationField('warehouseGroup', 'warehouse_group_id', WarehouseGroupDefinition::class, 'id'),
        ]);
    }
}
