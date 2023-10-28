<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1658302919AddMultiWarehouseEntities extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1658302919;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS `warehouse` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `product_warehouse` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NOT NULL,
                `warehouse_id` BINARY(16) NOT NULL,
                `stock` INT(11) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT `fk.product_warehouse.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.product_warehouse.warehouse_id` FOREIGN KEY (`warehouse_id`)
                    REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.product_id__warehouse_id` (`product_id`, `product_version_id`, `warehouse_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `warehouse_group` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NULL,
                `priority` INT NOT NULL DEFAULT 1,
                `rule_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT `fk.warehouse_group.rule_id` FOREIGN KEY (`rule_id`)
                    REFERENCES `rule` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `product_warehouse_group` (
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NOT NULL,
                `warehouse_group_id` BINARY(16) NOT NULL,
                CONSTRAINT `fk.product_warehouse_group.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.product_warehouse_group.warehouse_group_id` FOREIGN KEY (`warehouse_group_id`)
                    REFERENCES `warehouse_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                PRIMARY KEY (`product_id`, `product_version_id`, `warehouse_group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `warehouse_group_warehouse` (
                `warehouse_id` BINARY(16) NOT NULL,
                `warehouse_group_id` BINARY(16) NOT NULL,
                `priority` INT NOT NULL DEFAULT 1,
                CONSTRAINT `fk.warehouse_group_warehouse.warehouse_id` FOREIGN KEY (`warehouse_id`)
                    REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.warehouse_group_warehouse.warehouse_group_id` FOREIGN KEY (`warehouse_group_id`)
                    REFERENCES `warehouse_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                PRIMARY KEY (`warehouse_id`, `warehouse_group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `order_warehouse_group` (
                `id` BINARY(16) NOT NULL,
                `version_id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NOT NULL,
                `order_version_id` BINARY(16) NOT NULL,
                `warehouse_group_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT `fk.order_warehouse_group.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
                    REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.order_warehouse_group.warehouse_group_id` FOREIGN KEY (`warehouse_group_id`)
                    REFERENCES `warehouse_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                PRIMARY KEY (`id`, `version_id`),
                UNIQUE KEY `uniq.order_id__warehouse_group_id` (`order_id`, `order_version_id`, `warehouse_group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `order_product_warehouse` (
                `id` BINARY(16) NOT NULL,
                `version_id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NOT NULL,
                `order_version_id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NOT NULL,
                `warehouse_id` BINARY(16) NOT NULL,
                `quantity` INT(11) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT `fk.order_product_warehouse.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
                    REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.order_product_warehouse.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.order_product_warehouse.warehouse_id` FOREIGN KEY (`warehouse_id`)
                    REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                PRIMARY KEY (`id`, `version_id`),
                UNIQUE KEY `uniq.order_id__product_id__warehouse_id` (`order_id`, `order_version_id`, `product_id`, `product_version_id`, `warehouse_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
