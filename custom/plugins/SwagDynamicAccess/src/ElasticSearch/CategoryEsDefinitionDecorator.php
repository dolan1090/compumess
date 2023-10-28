<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\ElasticSearch;

use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\EnterpriseSearch\Indexing\AbstractElasticsearchDefinition;

class CategoryEsDefinitionDecorator extends AbstractElasticsearchDefinition
{
    private AbstractElasticsearchDefinition $decorated;

    private Connection $connection;

    public function __construct(AbstractElasticsearchDefinition $decorated, Connection $connection)
    {
        $this->decorated = $decorated;
        $this->connection = $connection;
    }

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->decorated->getEntityDefinition();
    }

    public function getMapping(Context $context): array
    {
        return \array_replace_recursive(
            $this->decorated->getMapping($context),
            [
                'properties' => [
                    'swagDynamicAccessRules' => [
                        'type' => 'nested',
                        'properties' => [
                            'id' => [
                                'type' => 'keyword',
                                'normalizer' => 'sw_lowercase_normalizer',
                            ],
                        ],
                    ],
                    'parent' => [
                        'type' => 'nested',
                        'properties' => [
                            'swagDynamicAccessRules' => [
                                'type' => 'nested',
                                'properties' => [
                                    'id' => [
                                        'type' => 'keyword',
                                        'normalizer' => 'sw_lowercase_normalizer',
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ]
        );
    }

    public function extendEntities(EntityCollection $collection): EntityCollection
    {
        return $this->decorated->extendEntities($collection);
    }

    public function extendDocuments(array $documents, Context $context): array
    {
        return $this->decorated->extendDocuments($documents, $context);
    }

    public function extendCriteria(Criteria $criteria): void
    {
        $this->decorated->extendCriteria($criteria);
    }

    public function fetch(array $ids, Context $context): array
    {
        $documents = $this->decorated->fetch($ids, $context);
        $categories = $this->fetchCategories($ids, $context);

        $uuids = \array_map(fn($id): string => Uuid::fromBytesToHex($id), $ids);
        $parentIds = \array_column($documents, 'parentId');
        $fetchingIds = \array_unique(\array_merge($parentIds, $uuids));
        $accessRules = $this->fetchDynamicAccessRules($fetchingIds);

        foreach ($documents as &$document) {
            $documentId = $document['id'];
            $document['swagDynamicAccessRules'] = [
                [
                    'id' => null
                ],
            ];

            if (isset($accessRules[$documentId])) {
                $document['swagDynamicAccessRules'] = \array_map(
                    fn ($ruleId) => ['id' => $ruleId]
                , \array_column($accessRules[$documentId], 'rule_id'));
            }

            $document['parent'] = [
                'swagDynamicAccessRules' => [
                    [
                        'id' => null
                    ],
                ]
            ];

            if (isset($categories[$documentId]['parentId'], $accessRules[$categories[$documentId]['parentId']])) {
                $parentId = $categories[$documentId]['parentId'];
                $document['parent']['swagDynamicAccessRules'] = \array_map(
                    fn ($ruleId) => ['id' => $ruleId]
                , \array_column($accessRules[$parentId], 'rule_id'));

            }
        }
        return $documents;
    }

    private function fetchDynamicAccessRules(array $categoryIds = []): array
    {
        $sql = <<<'SQL'
SELECT LOWER(HEX(category_id)) as category_id, LOWER(HEX(rule_id)) as rule_id FROM swag_dynamic_access_category_rule WHERE category_id in (?)
SQL;

        $data = $this->connection->fetchAllAssociative(
            $sql,
            [
                Uuid::fromHexToBytesList($categoryIds)
            ],
            [
                Connection::PARAM_STR_ARRAY
            ]
        );

        return FetchModeHelper::group($data);
    }

    private function fetchCategories(array $ids, Context $context): array
    {
        $sql = <<<'SQL'
SELECT
    LOWER(HEX(c.id)) AS id,
    LOWER(HEX(c.parent_id)) as parentId
FROM category c
    LEFT JOIN category pc ON c.parent_id = pc.id AND pc.version_id = :liveVersionId
WHERE c.id IN (:ids) AND c.version_id = :liveVersionId AND (c.child_count = 0 OR c.parent_id IS NOT NULL)
GROUP BY c.id
SQL;

        $data = $this->connection->fetchAllAssociative(
            $sql,
            [
                'ids' => $ids,
                'liveVersionId' => Uuid::fromHexToBytes($context->getVersionId()),
            ],
            [
                'ids' => Connection::PARAM_STR_ARRAY,
            ]
        );
        return FetchModeHelper::groupUnique($data);
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        return $this->decorated->buildTermQuery($context, $criteria);
    }
}
