<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1668072407UpdateLicenseScheduledTaskFrequency extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1668072407;
    }

    public function update(Connection $connection): void
    {
        $connection->update(
            'scheduled_task',
            ['run_interval' => 86400],
            ['name' => 'swag.commercial.update_license'],
        );
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
