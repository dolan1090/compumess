<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1677846662ReviewSummary extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1614903457;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `product_review_summary` (
          `id` BINARY(16) NOT NULL,
          `product_id` BINARY(16) NOT NULL,
          `product_version_id` BINARY(16) NOT NULL,
          `sales_channel_id` BINARY(16) NOT NULL,
          `created_at` DATETIME(3) NOT NULL,
          `updated_at` DATETIME(3) NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uniq.product_id__sales_channel_id` (`product_id`, `product_version_id`, `sales_channel_id`),
          KEY `idx.product_summary.product_id` (`product_id`,`product_version_id`),
          KEY `idx.product_summary.sales_channel_id` (`sales_channel_id`),
          CONSTRAINT `fk.product_summary.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
              REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk.product_summary.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
          REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `product_review_summary_translation` (
            `product_review_summary_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            `summary` LONGTEXT NULL,
            `visible` TINYINT NOT NULL DEFAULT 1,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`language_id`, `product_review_summary_id`),
            CONSTRAINT `fk.product_review_summary_translation.language_id` FOREIGN KEY (`language_id`)
                REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.product_review_summary_translation.product_review_summary_id` FOREIGN KEY (`product_review_summary_id`)
            REFERENCES `product_review_summary` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
