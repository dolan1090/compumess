<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1612967598CmsDraftAndActivity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1612967598;
    }

    public function update(Connection $connection): void
    {
        $playbook = [
            $this->getDraftsTableSchema(),
            $this->getActivityTableSchema(),
        ];

        foreach ($playbook as $query) {
            $connection->executeStatement($query);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // TODO: Implement updateDestructive() method.
    }

    private function getDraftsTableSchema(): string
    {
        return '
            CREATE TABLE IF NOT EXISTS `cms_page_draft` (
              `id` binary(16) NOT NULL,
              `cms_page_id` binary(16) NOT NULL,
              `cms_page_version_id` binary(16) NOT NULL,
              `name` varchar(255) NOT NULL,
              `draft_version_id` varchar(64) NOT NULL,
              `owner_user_id` binary(16) NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `deep_link_code` varchar(32) NOT NULL,
              `preview_media_id` binary(16) NULL,
              PRIMARY KEY (`id`, `cms_page_id`, `cms_page_version_id`),
              KEY `fk.cms_page_draft.cms_page_id` (`cms_page_id`, `cms_page_version_id`),
              KEY `fk.cms_page_draft.user_id` (`owner_user_id`),
              CONSTRAINT `fk.cms_page_draft.cms_page_id` FOREIGN KEY (`cms_page_id`, `cms_page_version_id`) REFERENCES `cms_page` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cms_page_draft.user_id` FOREIGN KEY (`owner_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.cms_page_draft.preview_media_id` FOREIGN KEY (`preview_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ';
    }

    private function getActivityTableSchema(): string
    {
        return '
            CREATE TABLE IF NOT EXISTS `cms_page_activity` (
              `id` binary(16) NOT NULL,
              `cms_page_id` binary(16) NOT NULL,
              `cms_page_version_id` binary(16) NOT NULL,
              `user_id` binary(16) NULL,
              `draft_version_id` varchar(64) NULL,
              `details` MEDIUMTEXT NULL,
              `is_merged` TINYINT(1) NOT NULL DEFAULT 0,
              `is_discarded` TINYINT(1) NOT NULL DEFAULT 0,
              `is_released_as_new` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) DEFAULT NULL,
              `name` VARCHAR(255) NOT NULL,
              PRIMARY KEY (`id`, `cms_page_id`, `cms_page_version_id`),
              KEY `fk.cms_page_activity.cms_page_id` (`cms_page_id`, `cms_page_version_id`),
              KEY `fk.cms_page_activity.user_id` (`user_id`),
              CONSTRAINT `fk.cms_page_activity.cms_page_id` FOREIGN KEY (`cms_page_id`, `cms_page_version_id`) REFERENCES `cms_page` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.cms_page_activity.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ';
    }
}
