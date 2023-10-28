<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Command\Helper;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class CleanupUnusedHelper
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function cleanupWarehouses(): int
    {
        return (int) $this->connection->executeStatement(<<<SQL
            DELETE FROM `warehouse`
            WHERE `id` IN (
                SELECT `id`
                FROM (
                    SELECT `warehouse`.`id`
                    FROM `warehouse`
                    LEFT JOIN `product_warehouse`
                        ON `warehouse`.`id` = `product_warehouse`.`warehouse_id`
                    LEFT JOIN `order_product_warehouse`
                        ON `warehouse`.`id` = `order_product_warehouse`.`warehouse_Id`
                    LEFT JOIN `warehouse_group_warehouse`
                        ON `warehouse`.`id` = `warehouse_group_warehouse`.`warehouse_id`
                    WHERE `product_warehouse`.`id` IS NULL
                        AND `order_product_warehouse`.`id` IS NULL
                        AND `warehouse_group_warehouse`.`warehouse_id` IS NULL
                ) AS orphans
            );
        SQL);
    }

    public function cleanupWarehouseGroups(): int
    {
        return (int) $this->connection->executeStatement(<<<SQL
            DELETE FROM `warehouse_group`
            WHERE `id` IN (
                SELECT `id`
                FROM (
                    SELECT `warehouse_group`.`id`
                    FROM `warehouse_group`
                    LEFT JOIN `rule`
                        ON `warehouse_group`.`rule_id` = `rule`.`id`
                    LEFT JOIN `warehouse_group_warehouse`
                        ON `warehouse_group`.`id` = `warehouse_group_warehouse`.`warehouse_group_id`
                    LEFT JOIN `product_warehouse_group`
                        ON `warehouse_group`.`id` = `product_warehouse_group`.`warehouse_group_id`
                    WHERE `rule`.`id` IS NULL
                        AND `warehouse_group_warehouse`.`warehouse_group_id` IS NULL
                        AND `product_warehouse_group`.`warehouse_group_id` IS NULL
                ) AS orphans
            );
        SQL);
    }
}
