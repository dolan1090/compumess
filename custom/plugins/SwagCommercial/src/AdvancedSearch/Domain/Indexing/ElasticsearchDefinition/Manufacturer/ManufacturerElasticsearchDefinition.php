<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Indexing\ElasticsearchDefinition\Manufacturer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Completion\CompletionDefinitionEnrichment;
use Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\AbstractCrossSearchLogic;
use Shopware\Commercial\AdvancedSearch\Domain\Search\AbstractSearchLogic;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('buyers-experience')]
class ManufacturerElasticsearchDefinition extends AbstractElasticsearchDefinition
{
    /**
     * @internal
     *
     * @param array<string, string> $languageAnalyzerMapping
     */
    public function __construct(
        private readonly ProductManufacturerDefinition $definition,
        private readonly Connection $connection,
        private readonly AbstractSearchLogic $searchLogic,
        private readonly AbstractCrossSearchLogic $crossSearchLogic,
        private readonly CompletionDefinitionEnrichment $completionDefinitionEnrichment,
        private readonly array $languageAnalyzerMapping
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getMapping(Context $context): array
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return [
                '_source' => ['includes' => ['id']],
                'properties' => [],
            ];
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

        $properties = [
            'name' => [
                'properties' => $languageFields,
            ],
            'description' => [
                'properties' => $languageFields,
            ],
        ];

        return [
            '_source' => ['includes' => ['id']],
            'properties' => array_merge($properties, $this->completionDefinitionEnrichment->enrichMapping()),
        ];
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        $mainQuery = $this->searchLogic->build($this->definition, $criteria, $context);

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
    public function fetch(array $ids, Context $context): array
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return [];
        }

        $data = $this->fetchManufacturers($ids, $context);

        $documents = [];

        foreach ($data as $id => $item) {
            /** @var array<int, array<string, string>> $translations */
            $translations = (array) json_decode($item['translation'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);

            $document = [
                'id' => $id,
                'name' => $this->mapTranslatedField('name', true, ...$translations),
                'description' => $this->mapTranslatedField('description', true, ...$translations),
            ];

            $documents[$id] = $document;
        }

        return $this->completionDefinitionEnrichment->enrichData($this->getEntityDefinition(), $documents);
    }

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<string, string>>
     */
    private function fetchManufacturers(array $ids, Context $context): array
    {
        $sql = <<<'SQL'
SELECT
    LOWER(HEX(manufacturer.id)) AS id,
    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'description', product_manufacturer_translation.description,
                    'name', product_manufacturer_translation.name,
                    'languageId', LOWER(HEX(product_manufacturer_translation.language_id))
                )
            ),
        ']'
    ) as translation
FROM product_manufacturer manufacturer
    LEFT JOIN product_manufacturer_translation ON (product_manufacturer_translation.product_manufacturer_id = manufacturer.id AND product_manufacturer_translation.product_manufacturer_version_id = manufacturer.version_id AND product_manufacturer_translation.name IS NOT NULL)
WHERE manufacturer.id IN (:ids) AND manufacturer.version_id = :liveVersionId
GROUP BY manufacturer.id
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
