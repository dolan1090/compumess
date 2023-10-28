<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\Boosting;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class BoostingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'advanced_search_boosting';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return BoostingCollection::class;
    }

    public function getEntityClass(): string
    {
        return BoostingEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
        ];
    }

    protected function getParentDefinitionClass(): ?string
    {
        return AdvancedSearchConfigDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            new DateTimeField('valid_from', 'validFrom'),
            new DateTimeField('valid_to', 'validTo'),
            (new FloatField('boost', 'boost'))->setFlags(new Required()),
            new BoolField('active', 'active'),
            (new StringField('name', 'name'))->setFlags(new Required()),

            new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class, 'id', false),

            new FkField('entity_stream_id', 'entityStreamId', EntityStreamDefinition::class),
            (new OneToOneAssociationField('entityStream', 'entity_stream_id', 'id', EntityStreamDefinition::class, false))->addFlags(new SetNullOnDelete()),

            (new FkField('config_id', 'configId', AdvancedSearchConfigDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('config', 'config_id', AdvancedSearchConfigDefinition::class, 'id', false),
        ]);
    }
}
