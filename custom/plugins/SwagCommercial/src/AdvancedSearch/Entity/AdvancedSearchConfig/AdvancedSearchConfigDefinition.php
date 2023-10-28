<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate\AdvancedSearchConfigFieldDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\Boosting\BoostingDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

#[Package('buyers-experience')]
class AdvancedSearchConfigDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'advanced_search_config';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AdvancedSearchConfigEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AdvancedSearchConfigCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'esEnabled' => true,
            'andLogic' => true,
            'minSearchLength' => 2,
            'hitCount' => [
                ProductDefinition::ENTITY_NAME => [
                    'maxSuggestCount' => 10,
                    'maxSearchCount' => null,
                ],
                CategoryDefinition::ENTITY_NAME => [
                    'maxSuggestCount' => 10,
                    'maxSearchCount' => 30,
                ],
                ProductManufacturerDefinition::ENTITY_NAME => [
                    'maxSuggestCount' => 10,
                    'maxSearchCount' => 30,
                ],
            ],
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new Required()),
            (new BoolField('es_enabled', 'esEnabled'))->addFlags(new Required()),
            (new BoolField('and_logic', 'andLogic'))->addFlags(new Required()),
            (new IntField('min_search_length', 'minSearchLength'))->addFlags(new Required()),
            (new JsonField('hit_count', 'hitCount', [
                new JsonField('product', 'product', [
                    new IntField('maxSuggestCount', 'maxSuggestCount'),
                    new IntField('maxSearchCount', 'maxSearchCount'),
                ]),
                new JsonField('product_manufacturer', 'product_manufacturer', [
                    new IntField('maxSuggestCount', 'maxSuggestCount'),
                    new IntField('maxSearchCount', 'maxSearchCount'),
                ]),
                new JsonField('category', 'category', [
                    new IntField('maxSuggestCount', 'maxSuggestCount'),
                    new IntField('maxSearchCount', 'maxSearchCount'),
                ]),
            ]))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
            (new OneToManyAssociationField('fields', AdvancedSearchConfigFieldDefinition::class, 'config_id', 'id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('boostings', BoostingDefinition::class, 'config_id', 'id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
