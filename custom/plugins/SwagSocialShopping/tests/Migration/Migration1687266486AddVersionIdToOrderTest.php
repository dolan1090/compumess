<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\Migration;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;

use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use SwagSocialShopping\Migration\Migration1687266486AddVersionIdToOrder;

/**
 * @internal
 * @covers \SwagSocialShopping\Migration\Migration1687266486AddVersionIdToOrder
 */
class Migration1687266486AddVersionIdToOrderTest extends TestCase
{
    use KernelTestBehaviour;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testMigrationShouldRunWithSqlRequirePrimaryKeyEnabled(): void
    {
        $this->revertMigration();

        $migration = new Migration1687266486AddVersionIdToOrder();
        $migration->update($this->connection);

        static::assertTrue(EntityDefinitionQueryHelper::columnExists($this->connection, 'swag_social_shopping_order', 'version_id'));

        $primaryKeys = array_values(array_filter($this->connection->createSchemaManager()->listTableIndexes('swag_social_shopping_order'), fn($index) => $index->isPrimary()));
        self::assertCount(1, $primaryKeys);

        $primaryColumns = $primaryKeys[0]->getColumns();
        self::assertCount(2, $primaryColumns);
        self::assertEqualsCanonicalizing(['id', 'version_id'], $primaryColumns);

        // execute the migration again to ensure that it can run multiple times
        $migration->update($this->connection);
    }

    private function revertMigration(): void
    {
        try {
            $this->connection->executeStatement(
                'ALTER TABLE `swag_social_shopping_order` DROP COLUMN `version_id`;'
            );
        } catch (\Exception) {

        }
    }
}

