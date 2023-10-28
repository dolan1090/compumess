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
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SocialShopping\Test\Helper\MigrationTemplateTestHelper;
use SwagSocialShopping\Migration\Migration1676904632FixProductExportAssignment;

class Migration1676904632FixProductExportAssignmentTest extends TestCase
{
    use IntegrationTestBehaviour;
    use MigrationTemplateTestHelper;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = KernelLifecycleManager::getConnection();
    }

    public function testMigrationUpdatesProductExportProductStreamId(): void
    {
        $salesChannelId = Uuid::randomHex();

        $validProductStreamId = $this->createProductStream(Uuid::randomHex());
        $invalidProductStreamId = $this->createProductStream(Uuid::randomHex());

        $productExport = $this->createProductExport([
            'sales_channel_id' => $salesChannelId,
            'product_stream_id' => $invalidProductStreamId
        ]);

        $this->createSocialSalesChannel([
            'sales_channel_id' => $salesChannelId,
            'product_stream_id' => $validProductStreamId
        ]);

        $migration = new Migration1676904632FixProductExportAssignment();
        $migration->update($this->connection);

        $productStreamId = $this->connection->fetchOne('SELECT `product_stream_id` FROM `product_export` WHERE `id` = :id', [
            'id' => Uuid::fromHexToBytes($productExport['id']),
        ]);

        static::assertEquals($validProductStreamId, Uuid::fromBytesToHex($productStreamId));
    }
}
