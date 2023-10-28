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

class Migration1602060888TemplateConfigurationVersionId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1602060888;
    }

    public function update(Connection $connection): void
    {
        $this->addVersionId($connection);

        $this->fillVersionIdWithDefaultForTable(
            'swag_customized_products_template_configuration',
            [
                'version_id',
            ],
            $connection
        );
        $this->fillVersionIdWithDefaultForTable(
            'swag_customized_products_template_configuration_share',
            [
                'version_id',
                'template_configuration_version_id',
            ],
            $connection
        );

        $this->addReferenceVersions($connection);
        $this->updateConstraintsAndIndices($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function fillVersionIdWithDefaultForTable(string $table, array $fieldsToUpdate, Connection $connection): void
    {
        $ids = $connection->executeQuery(\sprintf('SELECT `id` FROM `%s`', $table))->fetchFirstColumn();

        /** @var array $updateData */
        $updateData = [];
        foreach ($fieldsToUpdate as $field) {
            $updateData[$field] = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);
        }

        foreach ($ids as $id) {
            $connection->update(
                $table,
                $updateData,
                ['id' => $id]
            );
        }
    }

    private function addVersionId(Connection $connection): void
    {
        if (!$this->checkIfVersionIdColumnExist($connection)) {
            $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` ADD COLUMN `version_id` BINARY(16) AFTER `id`;');
        }

        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` ADD COLUMN `version_id` BINARY(16) AFTER `id`;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` ADD COLUMN `template_configuration_version_id` BINARY(16) AFTER `template_configuration_id`;');
    }

    private function addReferenceVersions(Connection $connection): void
    {
        if ($this->checkIfPrimaryKeyExist($connection)) {
            $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `version_id`);');
        } else {
            $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` ADD PRIMARY KEY (`id`, `version_id`);');
        }

        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` DROP KEY `uniq.swag_cupr_template_configuration__id`;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` ADD CONSTRAINT `uniq.swag_cupr_template_configuration__id` UNIQUE (`id`, `version_id`, `hash`);');

        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `version_id`);');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` DROP KEY `uniq.swag_cupr_template_configuration_share__id`;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` ADD CONSTRAINT `uniq.swag_cupr_template_configuration_share__id` UNIQUE (`id`, `version_id`, `template_configuration_id`);');
    }

    private function updateConstraintsAndIndices(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` ADD INDEX `fk.swag_cupr_configuration_share.template_version_id_index` (`version_id`, `id`);');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration` MODIFY `version_id` BINARY(16) NOT NULL;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` MODIFY `version_id` BINARY(16) NOT NULL;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` MODIFY `template_configuration_version_id` BINARY(16) NOT NULL;');
        $connection->executeStatement('ALTER TABLE `swag_customized_products_template_configuration_share` ADD CONSTRAINT `fk.swag_cupr_configuration_share.template_version_id` FOREIGN KEY (`template_configuration_version_id`) REFERENCES `swag_customized_products_template_configuration` (`version_id`) ON UPDATE CASCADE ON DELETE CASCADE;');
    }

    private function checkIfVersionIdColumnExist(Connection $connection): bool
    {
        $sql = <<<SQL
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA= DATABASE()
    AND TABLE_NAME = 'swag_customized_products_template_configuration'
    AND COLUMN_NAME = 'version_id';
SQL;

        $columnNameInDb = $connection->executeQuery($sql)->fetchOne();

        return $columnNameInDb !== false;
    }

    private function checkIfPrimaryKeyExist(Connection $connection): bool
    {
        $sql = <<<SQL
SELECT *
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'swag_customized_products_template_configuration'
    AND CONSTRAINT_TYPE = 'PRIMARY KEY';
SQL;
        $exists = $connection->executeQuery($sql)->fetchOne();

        return $exists !== false;
    }
}
