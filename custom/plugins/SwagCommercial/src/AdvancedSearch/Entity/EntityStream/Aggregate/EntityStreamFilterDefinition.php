<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate;

use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class EntityStreamFilterDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'advanced_search_entity_stream_filter';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return EntityStreamFilterEntity::class;
    }

    public function getCollectionClass(): string
    {
        return EntityStreamFilterCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return EntityStreamDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            (new FkField('entity_stream_id', 'entityStreamId', EntityStreamDefinition::class))->setFlags(new Required()),
            new ParentFkField(self::class),

            (new StringField('type', 'type'))->setFlags(new Required()),
            new StringField('field', 'field'),
            new StringField('operator', 'operator'),
            new LongTextField('value', 'value'),
            new JsonField('parameters', 'parameters'),
            new IntField('position', 'position'),

            new ManyToOneAssociationField('entityStream', 'entity_stream_id', EntityStreamDefinition::class, 'id', false),
            new ParentAssociationField(self::class, 'id'),
            new ChildrenAssociationField(self::class, 'queries'),
        ]);
    }
}
