<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('buyers-experience')]
class Migration1689144496SWAGAdvancedSearch_AddAdvancedSearchBoostingAndEntityStreams extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1689144496;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `advanced_search_entity_stream` (
              `id` BINARY(16) NOT NULL,
              `api_filter` JSON NULL,
              `type` VARCHAR(255) NOT NULL,
              `invalid`  TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.advanced_search_entity_stream.api_filter` CHECK (JSON_VALID(`api_filter`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `advanced_search_entity_stream_filter` (
              `id` BINARY(16) NOT NULL,
              `entity_stream_id` BINARY(16) NOT NULL,
              `parent_id` BINARY(16) NULL,
              `type` VARCHAR(255) NOT NULL,
              `field` VARCHAR(255) NULL,
              `operator` VARCHAR(255) NULL,
              `value` LONGTEXT NULL,
              `parameters` LONGTEXT NULL,
              `position` INT(11) DEFAULT 0 NOT NULL,
              `custom_fields` JSON NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.advanced_search_entity_stream_filter.entity_stream_id` FOREIGN KEY (`entity_stream_id`)
                REFERENCES `advanced_search_entity_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.advanced_search_entity_stream_filter.parent_id` FOREIGN KEY (`parent_id`)
                REFERENCES advanced_search_entity_stream_filter (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `advanced_search_boosting` (
              `id` BINARY(16) NOT NULL,
              `product_stream_id` BINARY(16) NULL,
              `entity_stream_id` BINARY(16) NULL,
              `config_id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `boost` DOUBLE DEFAULT 1 NOT NULL,
              `active` TINYINT(1) NOT NULL DEFAULT 1,
              `valid_from` DATETIME(3) NULL,
              `valid_to` DATETIME(3) NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.advanced_search_boosting.product_stream_id` FOREIGN KEY (`product_stream_id`)
                REFERENCES `product_stream` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.advanced_search_boosting.entity_stream_id` FOREIGN KEY (`entity_stream_id`)
                REFERENCES `advanced_search_entity_stream` (`id`) ON DELETE SET NULL,
              CONSTRAINT `fk.advanced_search_boosting.config_id` FOREIGN KEY (`config_id`)
                REFERENCES `advanced_search_config` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
