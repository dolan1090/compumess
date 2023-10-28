<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup\ProductWarehouseGroupDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse\WarehouseGroupWarehouseDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseGroupDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'warehouse_group';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WarehouseGroupEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WarehouseGroupCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            new LongTextField('description', 'description'),
            new IntField('priority', 'priority'),
            new FkField('rule_id', 'ruleId', RuleDefinition::class),
            new ManyToOneAssociationField(
                'rule',
                'rule_id',
                RuleDefinition::class,
                'id',
            ),
            new ManyToManyAssociationField(
                'warehouses',
                WarehouseDefinition::class,
                WarehouseGroupWarehouseDefinition::class,
                'warehouse_group_id',
                'warehouse_id'
            ),
            new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                ProductWarehouseGroupDefinition::class,
                'warehouse_group_id',
                'product_id'
            ),
        ]);
    }
}
