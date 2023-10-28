<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1691587641FixIntervalDefaultLanguage extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1691587641;
    }

    public function update(Connection $connection): void
    {
        $intervals = Migration1671702898SubscriptionInterval::DEFAULT_INTERVALS;

        foreach ($intervals as $interval) {
            $connection->executeStatement('
                INSERT INTO `subscription_interval_translation` (`subscription_interval_id`, `language_id`, `name`, `created_at`)
                VALUES (:intervalId, :defaultLanguage, :name, :createdAt)
                ON DUPLICATE KEY UPDATE
                `language_id` = :defaultLanguage, `subscription_interval_id` = :intervalId;
            ', [
                'intervalId' => Uuid::fromHexToBytes($interval['id']),
                'defaultLanguage' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'name' => $interval['name_enGB'],
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
