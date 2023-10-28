<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1689338315AddPermissionEntity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1689338315;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `b2b_permission` (
                `id`                    BINARY(16) NOT NULL,
                `name`                  VARCHAR(255) NOT NULL,
                `group`                 VARCHAR(255) NOT NULL,
                `dependencies`          JSON NOT NULL DEFAULT (JSON_ARRAY()),
                `created_at`            DATETIME(3) NOT NULL,
                `updated_at`            DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `uniq.b2b_permission.name` UNIQUE (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
