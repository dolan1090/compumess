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
#[Package('administration')]
class Migration1676898929MediaAiTag extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1676898929;
    }

    public function update(Connection $connection): void
    {
        $this->createTables($connection);

        $this->insertDefaultConfigValues($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createTables(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `media_ai_tag` (
    `id` BINARY(16) NOT NULL,
    `media_id` BINARY(16) NOT NULL,
    `needs_analysis` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk.media_ai_tag.media_id` FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `media_ai_tag_translation` (
    `media_ai_tag_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    `tags` JSON NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`media_ai_tag_id`, `language_id`),
    CONSTRAINT `json.media_ai_tag_translation.tags` CHECK (JSON_VALID(`tags`)),
    CONSTRAINT `fk.media_ai_tag_translation.media_ai_tag_id` FOREIGN KEY (`media_ai_tag_id`)
        REFERENCES `media_ai_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.media_ai_tag_translation.language_id` FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    private function insertDefaultConfigValues(Connection $connection): void
    {
        if (!$this->systemConfigKeyExists('core.mediaAiTag.enabled', $connection)) {
            $connection->insert(
                'system_config',
                [
                    'id' => Uuid::randomBytes(),
                    'configuration_key' => 'core.mediaAiTag.enabled',
                    'configuration_value' => \json_encode([
                        '_value' => false,
                    ]),
                    'sales_channel_id' => null,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        if (!$this->systemConfigKeyExists('core.mediaAiTag.targetLanguageId', $connection)) {
            $connection->insert(
                'system_config',
                [
                    'id' => Uuid::randomBytes(),
                    'configuration_key' => 'core.mediaAiTag.targetLanguageId',
                    'configuration_value' => \json_encode([
                        '_value' => Defaults::LANGUAGE_SYSTEM,
                    ]),
                    'sales_channel_id' => null,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        if (!$this->systemConfigKeyExists('core.mediaAiTag.addToAltText', $connection)) {
            $connection->insert(
                'system_config',
                [
                    'id' => Uuid::randomBytes(),
                    'configuration_key' => 'core.mediaAiTag.addToAltText',
                    'configuration_value' => \json_encode([
                        '_value' => true,
                    ]),
                    'sales_channel_id' => null,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        if ($this->systemConfigKeyExists('core.mediaAiTag.altTextStrategy', $connection)) {
            return;
        }

        $connection->insert(
            'system_config',
            [
                'id' => Uuid::randomBytes(),
                'configuration_key' => 'core.mediaAiTag.altTextStrategy',
                'configuration_value' => \json_encode([
                    '_value' => 'append',
                ]),
                'sales_channel_id' => null,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }

    private function systemConfigKeyExists(string $key, Connection $connection): bool
    {
        $qb = $connection->createQueryBuilder();

        return (bool) $qb->select('id')
            ->from('system_config')
            ->where('configuration_key = :key')
            ->setParameter('key', $key)
            ->fetchOne();
    }
}
