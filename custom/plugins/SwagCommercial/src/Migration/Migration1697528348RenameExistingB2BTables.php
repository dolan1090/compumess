<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1697528348RenameExistingB2BTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1697528348;
    }

    public function update(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();

        if (!$schemaManager->tablesExist('b2b_role') || !$this->columnExists($connection, 'b2b_role', 'business_partner_customer_id')) {
            return;
        }

        $schemaManager->renameTable('b2b_role', 'b2b_components_role');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
