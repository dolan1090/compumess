<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 */
#[Package('buyers-experience')]
final class DefaultAdvancedSearchConfig
{
    /**
     * @return array{es_enabled: int, and_logic: int, min_search_length: int, hit_count: string}
     */
    public static function getConfig(): array
    {
        $defaultHitCount = [
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
        ];

        return [
            'es_enabled' => 1,
            'and_logic' => 1,
            'min_search_length' => 2,
            'hit_count' => (string) json_encode($defaultHitCount),
        ];
    }

    /**
     * @return array<array{field: string, tokenize: int, searchable: int, ranking: float}>
     */
    public static function getConfigFields(string $entityName): array
    {
        $config = [
            ProductDefinition::ENTITY_NAME => self::getProductFields(),
            ProductManufacturerDefinition::ENTITY_NAME => self::getManufacturerFields(),
            CategoryDefinition::ENTITY_NAME => self::getCategoryFields(),
        ];

        return $config[$entityName] ?? [];
    }

    /**
     * @return array<array{field: string, tokenize: int, searchable: int, ranking: float}>
     */
    private static function getProductFields(): array
    {
        return [
            [
                'field' => 'productNumber',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 1000,
            ],
            [
                'field' => 'stock',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'availableStock',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'manufacturerNumber',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'ean',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'purchaseUnit',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'referenceUnit',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'purchasePrices.gross',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'purchasePrices.net',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'weight',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'width',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'height',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'length',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'sales',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'metaDescription',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'name',
                'tokenize' => 1,
                'searchable' => 1,
                'ranking' => 700,
            ],
            [
                'field' => 'keywords',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'description',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'metaTitle',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'packUnit',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'packUnitPlural',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'customSearchKeywords',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'manufacturer.name',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'unit.name',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'unit.shortCode',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'crossSellings.name',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'options.name',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 700,
            ],
            [
                'field' => 'properties.name',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'tags.name',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'categories.name',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
        ];
    }

    /**
     * @return array<array{field: string, tokenize: int, searchable: int, ranking: float}>
     */
    private static function getManufacturerFields(): array
    {
        return [
            [
                'field' => 'name',
                'tokenize' => 1,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'description',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.customSearchKeywords',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.metaTitle',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.description',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.keywords',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.name',
                'tokenize' => 1,
                'searchable' => 0,
                'ranking' => 700,
            ],
            [
                'field' => 'products.metaDescription',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.states',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.ean',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.manufacturerNumber',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.productNumber',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 1000,
            ],
        ];
    }

    /**
     * @return array<array{field: string, tokenize: int, searchable: int, ranking: float}>
     */
    private static function getCategoryFields(): array
    {
        return [
            [
                'field' => 'type',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'name',
                'tokenize' => 1,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'description',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'metaTitle',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'metaDescription',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'keywords',
                'tokenize' => 0,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'tags.name',
                'tokenize' => 1,
                'searchable' => 1,
                'ranking' => 500,
            ],
            [
                'field' => 'products.customSearchKeywords',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.metaTitle',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.description',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.keywords',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.name',
                'tokenize' => 1,
                'searchable' => 0,
                'ranking' => 700,
            ],
            [
                'field' => 'products.metaDescription',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.states',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 0,
            ],
            [
                'field' => 'products.ean',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.manufacturerNumber',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 500,
            ],
            [
                'field' => 'products.productNumber',
                'tokenize' => 0,
                'searchable' => 0,
                'ranking' => 1000,
            ],
        ];
    }
}
