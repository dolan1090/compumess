<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('business-ops')]
class Migration1679557857FixFlowViewerPrivileges extends MigrationStep
{
    final public const NEW_PRIVILEGES = [
        'flow.viewer' => [
            'swag_delay_action:read',
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1679557857;
    }

    public function update(Connection $connection): void
    {
        $this->addAdditionalPrivileges($connection, self::NEW_PRIVILEGES);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
