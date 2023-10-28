<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1677486096UpdateWarehouseGroupRuleOnDeleteBehaviour extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1677486096;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `warehouse_group` DROP FOREIGN KEY `fk.warehouse_group.rule_id`');
        $connection->executeStatement('ALTER TABLE `warehouse_group` ADD CONSTRAINT `fk.warehouse_group.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
