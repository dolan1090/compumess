<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('business-ops')]
class Migration1667205734CreateDelayActionTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1667205734;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `swag_delay_action` (
                `id` BINARY(16) NOT NULL,
                `event_name` VARCHAR(255) NOT NULL,
                `flow_id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NULL,
                `delay_sequence_id` BINARY(16) NOT NULL,
                `order_version_id` BINARY(16) NULL,
                `customer_id` BINARY(16) NULL,
                `stored` JSON NOT NULL,
                `execution_time` DATETIME(3) NOT NULL,
                `expired` BOOL NOT NULL DEFAULT FALSE,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.swag_delay_action.flow_id` FOREIGN KEY (`flow_id`)
                    REFERENCES `flow` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.swag_delay_action.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
                    REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.swag_delay_action.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.swag_delay_action.delay_sequence_id` FOREIGN KEY (`delay_sequence_id`)
                    REFERENCES `flow_sequence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
