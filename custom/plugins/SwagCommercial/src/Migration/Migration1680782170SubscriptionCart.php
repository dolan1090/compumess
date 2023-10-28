<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1680782170SubscriptionCart extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1680782170;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_cart` (
                `subscription_token`    VARCHAR(50) COLLATE utf8mb4_unicode_ci  NOT NULL,
                `main_token`            VARCHAR(50) COLLATE utf8mb4_unicode_ci  NOT NULL,
                `plan_id`               BINARY(16)                              NOT NULL,
                `interval_id`           BINARY(16)                              NOT NULL,
                `created_at`            DATETIME(3)                             NOT NULL,
                `updated_at`            DATETIME(3)                             NULL,
                PRIMARY KEY (`subscription_token`),
                INDEX (`main_token`),
                UNIQUE KEY (`main_token`, `plan_id`, `interval_id`),
                CONSTRAINT `fk.subscription_cart.subscription_token` FOREIGN KEY (`subscription_token`)
                    REFERENCES `cart` (`token`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_cart.plan_id` FOREIGN KEY (`plan_id`)
                    REFERENCES `subscription_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_cart.interval_id` FOREIGN KEY (`interval_id`)
                    REFERENCES `subscription_interval` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
