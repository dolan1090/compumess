<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\CustomizedProducts\Migration\Migration1602060888TemplateConfigurationVersionId;

class Migration1602060888TemplateConfigurationVersionIdTest extends TestCase
{
    use KernelTestBehaviour;

    private const ERROR_CODE_UNKNOWN_SYSTEM_VARIABLE = 1193;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testMigrationShouldRunWithSqlRequirePrimaryKeyEnabled(): void
    {
        $oldValue = $this->connection->fetchAllAssociative('SHOW VARIABLES like \'sql_require_primary_key\'');
        $this->setPrimaryKeyCheck('ON');

        $this->revertMigration();

        $migration = new Migration1602060888TemplateConfigurationVersionId();
        $migration->update($this->connection);

        $primaryKey = $this->connection->fetchAllAssociative(
            'SHOW KEYS FROM `swag_customized_products_template_configuration` WHERE Key_name = \'PRIMARY\''
        );
        static::assertCount(2, $primaryKey);

        $primaryKey = $this->connection->fetchAllAssociative(
            'SHOW KEYS FROM `swag_customized_products_template_configuration_share` WHERE Key_name = \'PRIMARY\''
        );
        static::assertCount(2, $primaryKey);

        if ($oldValue) {
            $this->setPrimaryKeyCheck($oldValue[0]['Value']);
        }
    }

    private function revertMigration(): void
    {
        $this->connection->executeStatement(
            'ALTER TABLE `swag_customized_products_template_configuration_share` DROP COLUMN `version_id`;'
        );
        $this->connection->executeStatement(
            'ALTER TABLE `swag_customized_products_template_configuration_share` DROP FOREIGN KEY `fk.swag_cupr_configuration_share.template_version_id`'
        );
        $this->connection->executeStatement(
            'ALTER TABLE `swag_customized_products_template_configuration_share` DROP COLUMN `template_configuration_version_id`;'
        );
        $this->connection->executeStatement(
            'ALTER TABLE `swag_customized_products_template_configuration` DROP  INDEX `fk.swag_cupr_configuration_share.template_version_id_index`'
        );
    }

    private function setPrimaryKeyCheck(string $value): void
    {
        try {
            $this->connection->executeStatement('SET SESSION sql_require_primary_key=' . $value);
        } catch (Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_UNKNOWN_SYSTEM_VARIABLE) {
                static::markTestSkipped('The system variable "sql_require_primary_key" is not supported by the database.');
            }

            throw $e;
        }
    }
}
