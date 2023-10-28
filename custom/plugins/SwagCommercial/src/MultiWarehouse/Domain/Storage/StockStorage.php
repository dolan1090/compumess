<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Storage;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('inventory')]
class StockStorage
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @param string[] $productIds
     *
     * @return array<string, int>
     */
    public function load(array $productIds, Context $context): array
    {
        $sql = <<<'SQL'
            SELECT LOWER(HEX(product.id)) AS product_id,
                   LOWER(HEX(product_warehouse_group.warehouse_group_id)) AS warehouse_group_id,
                   COALESCE(SUM(product_warehouse.stock), 0) AS stock,
                   warehouse_group.priority AS priority,
                   (warehouse_group.id IS NOT NULL) AS assigned
            FROM product
            INNER JOIN product_warehouse_group
                ON product.id = product_warehouse_group.product_id
                AND product.version_id = product_warehouse_group.product_version_id
            LEFT JOIN warehouse_group
                ON product_warehouse_group.warehouse_group_id = warehouse_group.id
                AND warehouse_group.rule_id IN (:ruleIds)
            LEFT JOIN warehouse_group_warehouse
                ON warehouse_group_warehouse.warehouse_group_id = product_warehouse_group.warehouse_group_id
            LEFT JOIN product_warehouse
                ON product_warehouse.product_id = product_warehouse_group.product_id
                AND product_warehouse.product_version_id = product_warehouse_group.product_version_id
                AND product_warehouse.warehouse_id = warehouse_group_warehouse.warehouse_id
            WHERE product.id IN (:ids)
            GROUP BY product_warehouse_group.warehouse_group_id,
                     product_warehouse_group.product_id
        SQL;

        /** @var array<int, array<string>> $stocks */
        $stocks = $this->connection->fetchAllAssociative(
            $sql,
            [
                'ids' => Uuid::fromHexToBytesList($productIds),
                'ruleIds' => Uuid::fromHexToBytesList($context->getRuleIds()),
            ],
            [
                'ids' => ArrayParameterType::STRING,
                'ruleIds' => ArrayParameterType::STRING,
            ]
        );

        $result = [];
        $priorities = [];

        foreach ($stocks as $data) {
            $assigned = (bool) $data['assigned'];
            $stock = (int) $data['stock'];

            $priority = (int) $data['priority'];
            $currentPriority = $priorities[$data['product_id']] ?? null;

            $isHigherPriority = $currentPriority === null || $currentPriority < $priority;

            if ($assigned && (!\array_key_exists($data['product_id'], $result) || $result[$data['product_id']] === null || $isHigherPriority)) {
                $result[$data['product_id']] = $stock;
                $priorities[$data['product_id']] = $priority;

                continue;
            }

            if (!$assigned && !\array_key_exists($data['product_id'], $result)) {
                $result[$data['product_id']] = null;
            }
        }

        return array_map('intval', $result);
    }
}
