<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Indexing\ElasticsearchDefinition\Category;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Completion\CompletionDefinitionEnrichment;
use Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\AbstractCrossSearchLogic;
use Shopware\Commercial\AdvancedSearch\Domain\Search\AbstractSearchLogic;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

#[Package('buyers-experience')]
class CategoryElasticsearchDefinition extends AbstractElasticsearchDefinition
{
    /**
     * @internal
     *
     * @param array<string, string> $languageAnalyzerMapping
     */
    public function __construct(
        private readonly CategoryDefinition $definition,
        private readonly Connection $connection,
        private readonly AbstractSearchLogic $searchLogic,
        private readonly AbstractCrossSearchLogic $crossSearchLogic,
        private readonly CompletionDefinitionEnrichment $completionDefinitionEnrichment,
        private readonly array $languageAnalyzerMapping
    ) {
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

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->definition;
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
            'metaTitle' => [
                'properties' => $languageFields,
            ],
            'metaDescription' => [
                'properties' => $languageFields,
            ],
            'keywords' => [
                'properties' => $languageFields,
            ],
            'type' => self::getTextFieldConfig(),
            'path' => self::getTextFieldConfig(),
            'tags' => [
                'type' => 'nested',
                'properties' => [
                    'id' => self::KEYWORD_FIELD,
                    'name' => self::KEYWORD_FIELD + self::SEARCH_FIELD,
                    '_count' => self::INT_FIELD,
                ],
            ],
        ];

        return [
            '_source' => ['includes' => ['id']],
            'properties' => array_merge($properties, $this->completionDefinitionEnrichment->enrichMapping()),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(array $ids, Context $context): array
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return [];
        }

        $categories = $this->fetchCategories($ids, $context);

        $documents = [];

        foreach ($categories as $id => $category) {
            /** @var array<int, array<string, string>> $translations */
            $translations = (array) json_decode($category['translation'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);
            /** @var array<int, array{id: string, languageId?: string}> $tags */
            $tags = json_decode($category['tags'] ?? '[]', true, 512, \JSON_THROW_ON_ERROR);

            $filteredTags = [];
            foreach ($tags as $tag) {
                if (empty($tag['id'])) {
                    continue;
                }

                $filteredTags[] = [
                    'id' => $tag['id'],
                    'name' => $this->stripText($tag['name'] ?? ''),
                    '_count' => 1,
                ];
            }

            $document = [
                'id' => $id,
                'path' => $category['path'],
                'type' => $category['type'],
                'active' => (bool) $category['active'],
                'metaTitle' => $this->mapTranslatedField('metaTitle', true, ...$translations),
                'keywords' => $this->mapTranslatedField('keywords', true, ...$translations),
                'metaDescription' => $this->mapTranslatedField('metaDescription', true, ...$translations),
                'name' => $this->mapTranslatedField('name', true, ...$translations),
                'description' => $this->mapTranslatedField('description', true, ...$translations),
                'tags' => $filteredTags,
            ];

            $documents[$id] = $document;
        }

        return $this->completionDefinitionEnrichment->enrichData($this->getEntityDefinition(), $documents);
    }

    /**
     * @param string[] $ids
     *
     * @return array<string, array<string, string>>
     */
    private function fetchCategories(array $ids, Context $context): array
    {
        $sql = <<<'SQL'
SELECT
    LOWER(HEX(category.id)) AS id,
    `type`,
    `active`,
    `path`,
    CONCAT(
        '[',
        GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'id', LOWER(HEX(tag.id)),
                    'name', tag.name
                )
            ),
        ']'
    ) as tags,
    CONCAT(
        '[',
            GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'description', category_translation.description,
                    'name', category_translation.name,
                    'id', LOWER(HEX(category_translation.category_id)),
                    'metaTitle', category_translation.meta_title,
                    'metaDescription', category_translation.meta_description,
                    'keywords', category_translation.keywords,
                    'languageId', LOWER(HEX(category_translation.language_id))
                )
            ),
        ']'
    ) as translation
FROM category
    LEFT JOIN category_translation ON (category.id = category_translation.category_id AND category.version_id = category_translation.category_version_id AND category_translation.name IS NOT NULL)
    LEFT JOIN category_tag ON (category_tag.category_id = category.id AND category_tag.category_version_id = category.version_id)
    LEFT JOIN tag ON (tag.id = category_tag.tag_id)
WHERE category.id IN (:ids) AND category.version_id = :liveVersionId
GROUP BY category.id
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
