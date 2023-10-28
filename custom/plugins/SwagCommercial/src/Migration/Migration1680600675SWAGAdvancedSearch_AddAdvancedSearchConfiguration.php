<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('buyers-experience')]
class Migration1680600675SWAGAdvancedSearch_AddAdvancedSearchConfiguration extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1680600675;
    }

    public function update(Connection $connection): void
    {
        $this->createAdvancedSearchConfigTable($connection);
        $this->createAdvancedSearchConfigFieldTable($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createAdvancedSearchConfigTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `advanced_search_config` (
                `id`                    BINARY(16)        NOT NULL,
                `sales_channel_id`      BINARY(16)        NOT NULL,
                `es_enabled`             TINYINT(1)       NOT NULL DEFAULT 1,
                `and_logic`             TINYINT(1)        NOT NULL DEFAULT 1,
                `min_search_length`     SMALLINT          NOT NULL DEFAULT 2,
                `hit_count` JSON NULL DEFAULT NULL,
                `created_at`            DATETIME(3)       NOT NULL,
                `updated_at`            DATETIME(3)       NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `uniq.advanced_search_config.sales_channel_id__es_enabled` UNIQUE (`sales_channel_id`, `es_enabled`),
                CONSTRAINT `fk.advanced_search_config.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                    REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ', []);
    }

    private function createAdvancedSearchConfigFieldTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `advanced_search_config_field` (
                `id`                            BINARY(16)                                  NOT NULL,
                `config_id`                     BINARY(16)                                  NOT NULL,
                `custom_field_id`               BINARY(16)                                  NULL,
                `entity`                         VARCHAR(255)                                NOT NULL,
                `field`                         VARCHAR(255)                                NOT NULL,
                `tokenize`                      TINYINT(1)                                  NOT NULL    DEFAULT 0,
                `searchable`                    TINYINT(1)                                  NOT NULL    DEFAULT 0,
                `ranking`                       INT(11)                                     NOT NULL    DEFAULT 0,
                `created_at`                    DATETIME(3)                                 NOT NULL,
                `updated_at`                    DATETIME(3)                                 NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `uniq.advanced_search_config_field.config_id__entity__field` UNIQUE (`config_id`, `entity`, `field`),
                CONSTRAINT `fk.advanced_search_config_field.config_id` FOREIGN KEY (`config_id`)
                    REFERENCES `advanced_search_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.advanced_search_config_field.custom_field_id` FOREIGN KEY (`custom_field_id`)
                    REFERENCES `custom_field` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
