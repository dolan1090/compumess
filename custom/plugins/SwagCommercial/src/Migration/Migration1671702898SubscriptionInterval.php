<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1671702898SubscriptionInterval extends MigrationStep
{
    public const DEFAULT_INTERVALS = [
        [
            'id' => '018877505a5e70008ad1945c4fc1bdbf',
            'name_enGB' => 'every week',
            'name_deDE' => 'jede Woche',
            'dateInterval' => 'P7D',
            'cronInterval' => '* * * * *',
        ],
        [
            'id' => '01887750c8f47000a3e7e24f9c2fb996',
            'name_enGB' => 'every second week',
            'name_deDE' => 'jede zweite Woche',
            'dateInterval' => 'P14D',
            'cronInterval' => '* * * * *',
        ],
        [
            'id' => '01887750ce4c7000b945e285ae1850c7',
            'name_enGB' => 'every first of the month',
            'name_deDE' => 'am ersten des Monats',
            'dateInterval' => 'P1D',
            'cronInterval' => '* * 1 * *',
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1671702898;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_interval` (
                `id`                    BINARY(16)      NOT NULL,
                `active`                TINYINT(1)      NOT NULL DEFAULT 1,
                `cron_interval`         VARCHAR(255)    NOT NULL,
                `date_interval`         VARCHAR(255)    NOT NULL,
                `availability_rule_id`  BINARY(16)      NULL,
                `created_at`            DATETIME(3)     NOT NULL,
                `updated_at`            DATETIME(3)     NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.subscription_interval.availability_rule_id` FOREIGN KEY (`availability_rule_id`)
                    REFERENCES `rule` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_interval_translation` (
                `subscription_interval_id`   BINARY(16)  NOT NULL,
                `language_id`                BINARY(16)  NOT NULL,
                `name`                       VARCHAR(255),
                `created_at`                 DATETIME(3) NOT NULL,
                `updated_at`                 DATETIME(3) NULL,
                PRIMARY KEY (`subscription_interval_id`, `language_id`),
                CONSTRAINT `fk.subscription_interval_translation.subscription_interval_id` FOREIGN KEY (`subscription_interval_id`)
                    REFERENCES `subscription_interval` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_interval_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $this->insertDefaultIntervals($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function insertDefaultIntervals(Connection $connection): void
    {
        $languages = $connection->fetchAllAssociativeIndexed('
            SELECT lo.code, la.id FROM `language` la LEFT JOIN `locale` as lo
            ON la.locale_id = lo.id WHERE lo.code IN ("en-GB", "de-DE")
        ');

        foreach (self::DEFAULT_INTERVALS as $interval) {
            $payload = array_merge($interval, [
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'languageId_enGB' => $languages['en-GB']['id'],
                'languageId_deDE' => $languages['de-DE']['id'],
            ]);

            $connection->executeStatement('
                INSERT INTO `subscription_interval` (`id`, `cron_interval`, `date_interval`, `created_at`)
                VALUES (UNHEX(:id), :cronInterval, :dateInterval, :createdAt)
                ON DUPLICATE KEY UPDATE id = id;
            ', $payload);

            $connection->executeStatement('
                INSERT INTO `subscription_interval_translation` (`subscription_interval_id`, `name`, `language_id`, `created_at`)
                VALUES
                    (UNHEX(:id), :name_deDE, :languageId_deDE, :createdAt),
                    (UNHEX(:id), :name_enGB, :languageId_enGB, :createdAt)
                ON DUPLICATE KEY UPDATE subscription_interval_id = subscription_interval_id;
            ', $payload);
        }
    }
}
