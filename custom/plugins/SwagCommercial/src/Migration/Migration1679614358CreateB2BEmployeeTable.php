<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1679614358CreateB2BEmployeeTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1673514358;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `b2b_employee` (
                `id`                    BINARY(16) NOT NULL,
                `active`                TINYINT(1) NOT NULL DEFAULT 1,
                `auto_increment`        BIGINT unsigned NOT NULL AUTO_INCREMENT,
                `business_partner_customer_id`   BINARY(16) NULL,
                `first_name`            VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `last_name`             VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `email`                 VARCHAR(254) COLLATE utf8mb4_unicode_ci NOT NULL,
                `password`              VARCHAR(1024) COLLATE utf8mb4_unicode_ci NULL,
                `recovery_time`         DATETIME(3) NULL,
                `recovery_hash`         VARCHAR(255) NULL,
                `custom_fields`         JSON NULL,
                `created_at`            DATETIME(3) NOT NULL,
                `updated_at`            DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                UNIQUE `uniq.auto_increment` (`auto_increment`),
                CONSTRAINT `fk.b2b_employee.business_partner_customer_id` FOREIGN KEY (`business_partner_customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
