<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1668518796SubscriptionPlan extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1668518796;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_plan` (
                `id`                    BINARY(16)      NOT NULL,
                `active`                TINYINT(1)      NOT NULL DEFAULT 1,
                `availability_rule_id`  BINARY(16)      NULL,
                `created_at`            DATETIME(3)     NOT NULL,
                `updated_at`            DATETIME(3)     NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.subscription_plan.availability_rule_id` FOREIGN KEY (`availability_rule_id`)
                    REFERENCES `rule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_plan_translation` (
                `subscription_plan_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NULL,
                `description` MEDIUMTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`subscription_plan_id`, `language_id`),
                CONSTRAINT `fk.subscription_plan_translation.subscription_plan_id` FOREIGN KEY (`subscription_plan_id`)
                    REFERENCES `subscription_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_plan_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
