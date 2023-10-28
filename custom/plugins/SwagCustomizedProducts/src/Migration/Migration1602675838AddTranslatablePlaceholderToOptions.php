<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation\TemplateOptionTranslationDefinition;

class Migration1602675838AddTranslatablePlaceholderToOptions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1602675838;
    }

    public function update(Connection $connection): void
    {
        $this->addPlaceholderColumn($connection);
        $this->migrateOldPlaceholderValues($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addPlaceholderColumn(Connection $connection): void
    {
        $queryString = <<<SQL
            ALTER TABLE `#table#`
                ADD COLUMN `placeholder` VARCHAR(255) AFTER `description`;
SQL;

        $sql = \str_replace(
            ['#table#'],
            [TemplateOptionTranslationDefinition::ENTITY_NAME],
            $queryString
        );
        $connection->executeStatement($sql);
    }

    private function migrateOldPlaceholderValues(Connection $connection): void
    {
        $queryString = <<<SQL
            UPDATE `#option_translation_table#` AS op_trans
            INNER JOIN `#option_table#` AS op
                ON op_trans.swag_customized_products_template_option_id = op.id
            SET op_trans.placeholder = JSON_UNQUOTE(JSON_EXTRACT(op.type_properties, '$.placeholder'))
            WHERE op_trans.language_id = '#language_default_id#'
SQL;

        $sql = \str_replace(
            [
                '#option_table#',
                '#option_translation_table#',
                '#language_default_id#',
            ],
            [
                TemplateOptionDefinition::ENTITY_NAME,
                TemplateOptionTranslationDefinition::ENTITY_NAME,
                Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            ],
            $queryString
        );
        $connection->executeStatement($sql);
    }
}
