<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1691673307AddMinimumExecution extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1691673307;
    }

    public function update(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $columns = $manager->listTableColumns(SubscriptionPlanDefinition::ENTITY_NAME);

        if (!\array_key_exists('minimum_execution_count', $columns)) {
            $connection->executeStatement('
            ALTER TABLE `subscription_plan` ADD COLUMN `minimum_execution_count` INT(11) unsigned NULL;
        ');
        }
        $columns = $manager->listTableColumns(SubscriptionDefinition::ENTITY_NAME);

        if (!\array_key_exists('initial_execution_count', $columns)) {
            $connection->executeStatement('
            ALTER TABLE `subscription` ADD COLUMN `initial_execution_count` INT(11) unsigned NOT NULL DEFAULT 0;
        ');
        }

        if (!\array_key_exists('remaining_execution_count', $columns)) {
            $connection->executeStatement('
            ALTER TABLE `subscription` ADD COLUMN `remaining_execution_count` INT(11) unsigned NOT NULL DEFAULT 0;
        ');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
