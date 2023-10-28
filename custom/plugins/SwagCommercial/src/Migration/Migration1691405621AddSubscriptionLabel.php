<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation\SubscriptionPlanTranslationDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1691405621AddSubscriptionLabel extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1691405621;
    }

    public function update(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $columns = $manager->listTableColumns(SubscriptionPlanTranslationDefinition::ENTITY_NAME);

        if (!\array_key_exists('label', $columns)) {
            $connection->executeStatement('ALTER TABLE `subscription_plan_translation` ADD COLUMN `label` VARCHAR(255) NULL;');
        }

        $columns = $manager->listTableColumns(SubscriptionPlanDefinition::ENTITY_NAME);

        if (!\array_key_exists('active_storefront_label', $columns)) {
            $connection->executeStatement('ALTER TABLE `subscription_plan` ADD COLUMN `active_storefront_label` tinyint(1) DEFAULT 0;');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
