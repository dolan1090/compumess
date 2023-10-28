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
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Tests\Migration\MigrationTestTrait;
use SwagSocialShopping\Migration\Migration1676535399UpdateSocialShoppingSalesChannelTypeIcon;

class Migration1676535399UpdateSocialShoppingSalesChannelTypeIconTest extends TestCase
{
    use MigrationTestTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = KernelLifecycleManager::getConnection();
    }

    public function testMigrationChangesIcon(): void
    {
        $id = Uuid::randomBytes();

        $this->connection->insert(
            'sales_channel_type',
            [
                'id' => $id,
                'icon_name' => 'default-shopping-basket',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_FORMAT),
            ]
        );

        $migration = new Migration1676535399UpdateSocialShoppingSalesChannelTypeIcon();

        $migration->update($this->connection);

        $name = $this->connection->fetchOne('SELECT icon_name FROM `sales_channel_type` WHERE `id` = :id', [
            'id' => $id,
        ]);

        static::assertEquals('regular-shopping-basket', $name);
    }
}
