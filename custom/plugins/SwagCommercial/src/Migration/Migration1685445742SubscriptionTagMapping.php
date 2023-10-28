<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1685445742SubscriptionTagMapping extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1685445742;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_tag_mapping` (
                `subscription_id`  BINARY(16)   NOT NULL,
                `tag_id`           BINARY(16)   NOT NULL,
                PRIMARY KEY (`subscription_id`, `tag_id`),
                CONSTRAINT `fk.subscription_tag_mapping.subscription_id` FOREIGN KEY (`subscription_id`)
                    REFERENCES `subscription` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_tag_mapping.tag_id` FOREIGN KEY (`tag_id`)
                    REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
