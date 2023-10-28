<?php declare(strict_types=1);

namespace Swag\SocialShopping\Test\Migration;

/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SocialShopping\Test\Helper\MigrationTemplateTestHelper;
use SwagSocialShopping\Migration\Migration1659081754AddAclRights;

class Migration1659081754AddAclRightsTest extends TestCase
{
    use IntegrationTestBehaviour;
    use MigrationTemplateTestHelper;

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getConnection();
    }

    public function testMigration(): void
    {
        $migration = new Migration1659081754AddAclRights();

        $repo = $this->getContainer()->get('acl_role.repository');
        $id = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $repo->create([[
            'id' => $id,
            'name' => 'test',
            'privileges' => ['order.viewer', 'customer.viewer'],
        ]], $context);

        $migration->update($this->connection);

        $role = $repo->search(new Criteria([$id]), $context)->first();

        static::assertNotNull($role);

        static::assertContains('swag_social_shopping_order:read', $role->getPrivileges());
        static::assertContains('sales_channel_type:read', $role->getPrivileges());
        static::assertContains('swag_social_shopping_customer:read', $role->getPrivileges());
    }
}
