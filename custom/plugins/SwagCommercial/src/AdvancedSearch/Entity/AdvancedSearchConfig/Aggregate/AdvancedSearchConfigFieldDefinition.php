<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\CustomField\CustomFieldDefinition;

#[Package('buyers-experience')]
class AdvancedSearchConfigFieldDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'advanced_search_config_field';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AdvancedSearchConfigFieldEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AdvancedSearchConfigFieldCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'tokenize' => false,
            'searchable' => false,
            'ranking' => 0,
        ];
    }

    protected function getParentDefinitionClass(): ?string
    {
        return AdvancedSearchConfigDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('config_id', 'configId', AdvancedSearchConfigDefinition::class))->addFlags(new Required()),
            new FkField('custom_field_id', 'customFieldId', CustomFieldDefinition::class),
            (new StringField('entity', 'entity'))->addFlags(new Required()),
            (new StringField('field', 'field'))->addFlags(new Required()),
            (new BoolField('tokenize', 'tokenize'))->addFlags(new Required()),
            (new BoolField('searchable', 'searchable'))->addFlags(new Required()),
            (new IntField('ranking', 'ranking'))->addFlags(new Required()),
            new ManyToOneAssociationField('config', 'config_id', AdvancedSearchConfigDefinition::class, 'id', false),
            new ManyToOneAssociationField('customField', 'custom_field_id', CustomFieldDefinition::class, 'id', false),
        ]);
    }
}
