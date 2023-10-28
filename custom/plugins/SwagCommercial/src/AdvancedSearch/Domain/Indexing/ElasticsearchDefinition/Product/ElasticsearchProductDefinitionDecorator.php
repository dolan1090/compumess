<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Indexing\ElasticsearchDefinition\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Completion\CompletionDefinitionEnrichment;
use Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\AbstractCrossSearchLogic;
use Shopware\Commercial\AdvancedSearch\Domain\Search\AbstractSearchLogic;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('buyers-experience')]
class ElasticsearchProductDefinitionDecorator extends AbstractElasticsearchDefinition
{
    /**
     * @internal
     *
     * @param array<string, string> $languageAnalyzerMapping
     */
    public function __construct(
        private readonly AbstractElasticsearchDefinition $decorated,
        private readonly Connection $connection,
        private readonly AbstractSearchLogic $searchLogic,
        private readonly AbstractCrossSearchLogic $crossSearchLogic,
        private readonly CompletionDefinitionEnrichment $completionDefinitionEnrichment,
        private readonly array $languageAnalyzerMapping
    ) {
    }

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->decorated->getEntityDefinition();
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $this->decorated->buildTermQuery($context, $criteria);
        }

        $mainQuery = $this->searchLogic->build($this->getEntityDefinition(), $criteria, $context);
        $crossQuery = $this->crossSearchLogic->build($this->getEntityDefinition(), $criteria, $context);

        if (empty($crossQuery->getQueries())) {
            return $mainQuery;
        }

        $crossQuery->add($mainQuery, BoolQuery::SHOULD);

        return $crossQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(Context $context): array
    {
        $mappings = $this->decorated->getMapping($context);

        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $mappings;
        }

        /** @var array<string, string> $languages */
        $languages = $this->connection->fetchAllKeyValue(
            'SELECT LOWER(HEX(language.`id`)) as id, locale.code
             FROM language
             INNER JOIN locale ON locale_id = locale.id'
        );

        $languageFields = [];

        foreach ($languages as $languageId => $code) {
            $parts = explode('-', $code);
            $locale = $parts[0];

            $languageFields[$languageId] = self::getTextFieldConfig();
            if (\array_key_exists($locale, $this->languageAnalyzerMapping)) {
                /** @var array<string, array<string, string>> $fields */
                $fields = $languageFields[$languageId]['fields'];
                $fields['search']['analyzer'] = $this->languageAnalyzerMapping[$locale];
                $languageFields[$languageId]['fields'] = $fields;
            }
        }

        $additionalMappings = [
            'purchasePrices' => [
                'properties' => [
                    'net' => self::FLOAT_FIELD,
                    'gross' => self::FLOAT_FIELD,
                ],
            ],
            'referenceUnit' => self::FLOAT_FIELD,
            'purchaseUnit' => self::FLOAT_FIELD,
            'keywords' => [
                'properties' => $languageFields,
            ],
            'packUnit' => [
                'properties' => $languageFields,
            ],
            'packUnitPlural' => [
                'properties' => $languageFields,
            ],
            'unit' => [
                'type' => 'nested',
                'properties' => [
                    'id' => self::KEYWORD_FIELD,
                    '_count' => self::INT_FIELD,
                    'shortCode' => [
                        'properties' => $languageFields,
                    ],
                    'name' => [
                        'properties' => $languageFields,
                    ],
                ],
            ],
            'crossSellings' => [
                'type' => 'nested',
                'properties' => [
                    'id' => self::KEYWORD_FIELD,
                    '_count' => self::INT_FIELD,
                    'name' => [
                        'properties' => $languageFields,
                    ],
                ],
            ],
        ];

        $additionalMappings = array_merge($additionalMappings, $this->completionDefinitionEnrichment->enrichMapping());

        foreach ($additionalMappings as $mappingKey => $additionalMapping) {
            if (\array_key_exists('properties', $additionalMapping) && \array_key_exists($mappingKey, $mappings['properties']) && \is_array($additionalMapping['properties'])) {
                $additionalMapping['properties'] = array_merge($additionalMapping['properties'], $mappings['properties'][$mappingKey]['properties'] ?? []);
            }

            $mappings['properties'][$mappingKey] = $additionalMapping;
        }

        return $mappings;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(array $ids, Context $context): array
    {
        $data = $this->decorated->fetch($ids, $context);

        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $data;
        }

        $additionalData = $this->fetchProducts($ids, $context);

        $documents = [];

        /** @var array<string, string> $item */
        foreach ($additionalData as $id => $item) {
            /** @var array<int, array<string, string>> $translations */
            $translations = (array) json_decode($item['translation'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);
            /** @var array<int, array<string, string>> $parentTranslations */
            $parentTranslations = (array) json_decode($item['translation_parent'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);
            /** @var array<mixed> $purchasePrices */
            $purchasePrices = (array) json_decode($item['purchasePrices'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);
            /** @var array<int, array{id: string, languageId?: string}> $crossSellings */
            $crossSellings = (array) json_decode($item['cross_selling_translation'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);
            /** @var array<int, array{id: string, languageId?: string}> $units */
            $units = (array) json_decode($item['unit_translation'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);

            $document = [
                'purchasePrices' => array_values($purchasePrices),
                'referenceUnit' => $item['referenceUnit'] ?? null,
                'purchaseUnit' => $item['purchaseUnit'] ?? null,
                'keywords' => $this->mapTranslatedField('keywords', true, ...$parentTranslations, ...$translations),
                'packUnit' => $this->mapTranslatedField('packUnit', true, ...$parentTranslations, ...$translations),
                'packUnitPlural' => $this->mapTranslatedField('packUnitPlural', true, ...$parentTranslations, ...$translations),
                'unit' => [
                    'id' => $item['unitId'],
                    ...$this->mapToOneAssociations($units, ['name', 'short_code']),
                    '_count' => 1,
                ],
                'crossSellings' => $this->mapToManyAssociations($crossSellings, ['name']),
            ];

            $document = array_merge($document, $data[$id]);

            $documents[$id] = $document;
        }

        return $this->completionDefinitionEnrichment->enrichData($this->getEntityDefinition(), $documents);
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<string, string>>
     */
    private function fetchProducts(array $ids, Context $context): array
    {
        $sql = <<<'SQL'
SELECT
    LOWER(HEX(p.id)) AS id,

    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'languageId', LOWER(HEX(product_main.language_id)),
                    'packUnit', product_main.pack_unit,
                    'packUnitPlural', product_main.pack_unit_plural,
                    'keywords', product_main.keywords
                )
            ),
        ']'
    ) as translation,
    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'languageId', LOWER(HEX(product_parent.language_id)),
                    'packUnit', product_parent.pack_unit,
                    'packUnitPlural', product_parent.pack_unit_plural,
                    'keywords', product_parent.keywords
                )
            ),
        ']'
    ) as translation_parent,
    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'id', LOWER(HEX(product_cross_selling_translation.product_cross_selling_id)),
                    'languageId', LOWER(HEX(product_cross_selling_translation.language_id)),
                    'name', product_cross_selling_translation.name
                )
            ),
        ']'
    ) as cross_selling_translation,
    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'name', unit_translation.name,
                    'languageId', LOWER(HEX(unit_translation.language_id)),
                    'short_code', unit_translation.short_code
                )
            ),
        ']'
    ) as unit_translation,
    LOWER(HEX(IFNULL(p.unit_id, pp.unit_id))) AS unitId,
    IFNULL(p.reference_unit, pp.reference_unit) AS referenceUnit,
    IFNULL(p.purchase_unit, pp.purchase_unit) AS purchaseUnit,
    IFNULL(p.purchase_prices, pp.purchase_prices) AS purchasePrices
FROM product p
    LEFT JOIN product pp ON(p.parent_id = pp.id AND pp.version_id = :liveVersionId)
    LEFT JOIN product_translation product_main ON product_main.product_id = p.id AND product_main.product_version_id = p.version_id
    LEFT JOIN product_translation product_parent ON product_parent.product_id = p.parent_id AND product_parent.product_version_id = p.version_id
    LEFT JOIN product_cross_selling_translation ON product_cross_selling_translation.product_cross_selling_id = p.crossSellings
    LEFT JOIN unit_translation ON unit_translation.unit_id = p.unit
WHERE p.id IN (:ids) AND p.version_id = :liveVersionId AND (p.child_count = 0 OR p.parent_id IS NOT NULL OR JSON_EXTRACT(`p`.`variant_listing_config`, "$.displayParent") = 1)
GROUP BY p.id
SQL;

        /** @var array<string, array<string, string>> $result */
        $result = $this->connection->fetchAllAssociativeIndexed(
            $sql,
            [
                'ids' => $ids,
                'liveVersionId' => Uuid::fromHexToBytes($context->getVersionId()),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );

        return $result;
    }
}
