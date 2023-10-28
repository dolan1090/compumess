<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594373672TemplateConfiguration extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594373672;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_configuration` (
    `id`                  BINARY(16)  NOT NULL,
    `hash`                VARCHAR(32) NOT NULL,
    `configuration`       JSON        NOT NULL,
    `template_id`         BINARY(16)  NOT NULL,
    `template_version_id` BINARY(16)  NOT NULL,
    `created_at`          DATETIME(3) NOT NULL,
    `updated_at`          DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `uniq.swag_cupr_template_configuration__id` UNIQUE (`id`, `hash`),
    CONSTRAINT `fk.swag_cupr_template_configuration.template_id` FOREIGN KEY (`template_id`, `template_version_id`)
        REFERENCES `swag_customized_products_template` (`id`, `version_id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `json.swag_cupr_template_configuration.configuration` CHECK (JSON_VALID(`configuration`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
