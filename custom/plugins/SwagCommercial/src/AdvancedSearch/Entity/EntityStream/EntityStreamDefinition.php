<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream;

use Shopware\Commercial\AdvancedSearch\Entity\Boosting\BoostingDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate\EntityStreamFilterDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class EntityStreamDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'advanced_search_entity_stream';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EntityStreamCollection::class;
    }

    public function getEntityClass(): string
    {
        return EntityStreamEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            (new StringField('type', 'type'))->setFlags(new Required()),
            (new JsonField('api_filter', 'apiFilter'))->setFlags(new WriteProtected()),
            (new BoolField('invalid', 'invalid'))->setFlags(new WriteProtected()),
            (new OneToManyAssociationField('filters', EntityStreamFilterDefinition::class, 'entity_stream_id'))->addFlags(new CascadeDelete()),
            new OneToOneAssociationField('boosting', 'id', 'entity_stream_id', BoostingDefinition::class, false),
        ]);
    }
}
