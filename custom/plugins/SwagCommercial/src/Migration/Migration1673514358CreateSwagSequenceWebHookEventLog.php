<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('business-ops')]
class Migration1673514358CreateSwagSequenceWebHookEventLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1673514358;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `swag_sequence_webhook_event_log` (
                `sequence_id` BINARY(16) NOT NULL,
                `webhook_event_log_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`sequence_id`, `webhook_event_log_id`),
                CONSTRAINT `fk.swag_sequence_webhook_event_log.sequence_id` FOREIGN KEY (`sequence_id`)
                    REFERENCES `flow_sequence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.swag_sequence_webhook_event_log.webhook_event_log_id` FOREIGN KEY (`webhook_event_log_id`)
                    REFERENCES `webhook_event_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
