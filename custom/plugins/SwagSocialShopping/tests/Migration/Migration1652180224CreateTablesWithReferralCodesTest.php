<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use SwagSocialShopping\Migration\Migration1652180224CreateTablesWithReferralCodes;

class Migration1652180224CreateTablesWithReferralCodesTest extends TestCase
{
    use KernelTestBehaviour;

    private const REFERRAL_CODE = 'referral_code';
    private const SOC_CUSTOMER_TABLE = 'swag_social_shopping_customer';
    private const SOC_ORDER_TABLE = 'swag_social_shopping_order';
    private const SOC_SALES_CHANNEL_TABLE = 'swag_social_shopping_sales_channel';

    private AbstractSchemaManager $schemaManager;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->schemaManager = $this->connection->createSchemaManager();
    }

    public function testMigrationCreatesTablesWithReferralCodes(): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS `swag_social_shopping_order`');
        $this->connection->executeStatement('DROP TABLE IF EXISTS `swag_social_shopping_customer`');

        $migration = new Migration1652180224CreateTablesWithReferralCodes();
        $migration->update($this->connection);

        static::assertTrue($this->schemaManager->tablesExist(self::SOC_CUSTOMER_TABLE));
        static::assertTrue($this->schemaManager->tablesExist(self::SOC_ORDER_TABLE));

        $columns = $this->schemaManager->listTableColumns(self::SOC_CUSTOMER_TABLE);
        static::assertArrayHasKey(self::REFERRAL_CODE, $columns);

        $columns = $this->schemaManager->listTableColumns(self::SOC_ORDER_TABLE);
        static::assertArrayHasKey(self::REFERRAL_CODE, $columns);
    }
}
