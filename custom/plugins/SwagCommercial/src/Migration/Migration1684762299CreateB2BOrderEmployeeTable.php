<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1684762299CreateB2BOrderEmployeeTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1682322727;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `b2b_order_employee` (
              `id` BINARY(16) NOT NULL,
              `version_id` BINARY(16) NOT NULL,
              `order_id` BINARY(16) NOT NULL,
              `order_version_id` BINARY(16) NOT NULL,
              `employee_id` BINARY(16),
              `first_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `last_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`, `version_id`),
              CONSTRAINT `fk.order_employee.order_id__order_version_id` FOREIGN KEY (`order_id`, `order_version_id`)
                REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.order_employee.employee_id` FOREIGN KEY (`employee_id`)
                REFERENCES `b2b_employee` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              UNIQUE KEY `uniq.order_id__employee_id` (`order_id`, `order_version_id`, `employee_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
