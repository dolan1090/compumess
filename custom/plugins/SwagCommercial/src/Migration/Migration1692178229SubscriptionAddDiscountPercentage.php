<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1692178229SubscriptionAddDiscountPercentage extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1692178229;
    }

    public function update(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $columns = $manager->listTableColumns(SubscriptionPlanDefinition::ENTITY_NAME);

        if (\array_key_exists('discount_percentage', $columns)) {
            return;
        }

        $connection->executeStatement('
            ALTER TABLE `subscription_plan` ADD COLUMN `discount_percentage` DECIMAL(7,4) NOT NULL DEFAULT 0;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
