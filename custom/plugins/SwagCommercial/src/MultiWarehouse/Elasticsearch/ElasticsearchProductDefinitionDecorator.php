<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Elasticsearch;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Shopware\Elasticsearch\Product\ElasticsearchProductDefinition;

#[Package('inventory')]
class ElasticsearchProductDefinitionDecorator extends AbstractElasticsearchDefinition
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractElasticsearchDefinition $decorated,
        private readonly Connection $connection
    ) {
    }

    public function getEntityDefinition(): EntityDefinition
    {
        return $this->decorated->getEntityDefinition();
    }

    public function buildTermQuery(Context $context, Criteria $criteria): BoolQuery
    {
        return $this->decorated->buildTermQuery($context, $criteria);
    }

    public function getMapping(Context $context): array
    {
        /** @var array{_source: array{includes: string[]}, properties: array<mixed>} $mapping */
        $mapping = $this->decorated->getMapping($context);

        $mapping['properties']['warehouseGroups'] = [
            'type' => 'nested',
            'properties' => [
                'id' => ElasticsearchProductDefinition::KEYWORD_FIELD,
                'priority' => ElasticsearchProductDefinition::INT_FIELD,
                'ruleId' => ElasticsearchProductDefinition::KEYWORD_FIELD,
                '_count' => ElasticsearchProductDefinition::INT_FIELD,
            ],
        ];

        return $mapping;
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<mixed>>
     */
    public function fetch(array $ids, Context $context): array
    {
        /** @var array<string, array<mixed>> $documents */
        $documents = $this->decorated->fetch($ids, $context);

        $sql = <<<'SQL'
            SELECT LOWER(HEX(wg.id)) AS id,
                   wg.priority as priority,
                   LOWER(HEX(wg.rule_id)) AS ruleId,
                   LOWER(HEX(pwg.product_id)) as productId
            FROM warehouse_group as wg
            INNER JOIN product_warehouse_group as pwg
                ON wg.id = pwg.warehouse_group_id
            WHERE pwg.product_id IN (:ids)
            AND pwg.product_version_id = :version
        SQL;

        /** @var array<int, array<string>> $warehouseGroups */
        $warehouseGroups = $this->connection->fetchAllAssociative(
            $sql,
            [
                'ids' => $ids,
                'version' => Uuid::fromHexToBytes($context->getVersionId()),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );

        foreach ($warehouseGroups as $warehouseGroup) {
            $productId = $warehouseGroup['productId'];
            $documents[$productId]['warehouseGroups'][] = [
                'id' => $warehouseGroup['id'],
                'priority' => (int) $warehouseGroup['priority'],
                'ruleId' => $warehouseGroup['ruleId'],
                '_count' => 1,
            ];
        }

        return $documents;
    }
}
