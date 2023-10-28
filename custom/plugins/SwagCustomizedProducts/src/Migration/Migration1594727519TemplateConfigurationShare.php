<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1594727519TemplateConfigurationShare extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594727519;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_configuration_share` (
    `id`                        BINARY(16)  NOT NULL,
    `template_configuration_id` BINARY(16)  NOT NULL,
    `one_time`                  TINYINT(1)  NOT NULL DEFAULT 0,
    `created_at`                DATETIME(3) NOT NULL,
    `updated_at`                DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `uniq.swag_cupr_template_configuration_share__id` UNIQUE (`id`, `template_configuration_id`),
    CONSTRAINT `fk.swag_cupr_template_configuration_share.configuration_id` FOREIGN KEY (`template_configuration_id`)
        REFERENCES `swag_customized_products_template_configuration` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
