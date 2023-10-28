<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1668519108SubscriptionPlanProductMapping extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1668519108;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_plan_product_mapping` (
                `product_id`                BINARY(16)   NOT NULL,
                `product_version_id`        BINARY(16)   NOT NULL,
                `subscription_plan_id`      BINARY(16)   NOT NULL,
                PRIMARY KEY (`product_id`, `product_version_id`, `subscription_plan_id`),
                CONSTRAINT `fk.subscription_plan_product_mapping.subscription_plan_id` FOREIGN KEY (`subscription_plan_id`)
                    REFERENCES `subscription_plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_plan_product_mapping.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
