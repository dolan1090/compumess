<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1683977387SubscriptionAddOrderForeignKey extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1683977387;
    }

    public function update(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $columns = $manager->listTableColumns(OrderDefinition::ENTITY_NAME);

        if (\array_key_exists('subscription_id', $columns)) {
            return;
        }

        $connection->executeStatement('
            ALTER TABLE `order`
                ADD COLUMN `subscription_id` BINARY(16) NULL,
            ADD CONSTRAINT `fk.order.subscription_id` FOREIGN KEY (`subscription_id`)
                REFERENCES `subscription` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
