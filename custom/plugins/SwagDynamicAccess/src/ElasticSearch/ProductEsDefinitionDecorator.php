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
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;

class ProductEsDefinitionDecorator extends AbstractElasticsearchDefinition
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

    /**
     * @param array<mixed> $documents
     *
     * @return array<mixed>
     */
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

        $uuids = \array_map(fn($id): string => Uuid::fromBytesToHex($id), $ids);
        $parentIds = \array_filter(\array_column($documents, 'parentId'));
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
                , \array_column($accessRules[$document['id']], 'rule_id'));
            }

            $document['parent'] = [
                'swagDynamicAccessRules' => [
                    [
                        'id' => null
                    ],
                ]
            ];

            if (isset($document['parentId'], $accessRules[$document['parentId']])) {
                $document['parent']['swagDynamicAccessRules'] = \array_map(
                    fn ($ruleId) => ['id' => $ruleId]
                , \array_column($accessRules[$document['parentId']], 'rule_id'));
            }
        }

        return $documents;
    }

    /**
     * @param array<string> $productIds
     *
     * @return array<string>
     */
    private function fetchDynamicAccessRules(array $productIds = []): array
    {
        $sql = 'SELECT LOWER(HEX(product_id)) as product_id, LOWER(HEX(rule_id)) as rule_id FROM swag_dynamic_access_product_rule WHERE product_id in (?)';

        $data = $this->connection->fetchAllAssociative($sql, [Uuid::fromHexToBytesList($productIds)], [Connection::PARAM_STR_ARRAY]);

        return FetchModeHelper::group($data);
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        return $this->decorated->buildTermQuery($context, $criteria);
    }
}
