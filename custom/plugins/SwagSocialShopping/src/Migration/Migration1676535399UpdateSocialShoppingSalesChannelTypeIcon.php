<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1676535399UpdateSocialShoppingSalesChannelTypeIcon extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1676535399;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            UPDATE `sales_channel_type`
            SET `icon_name` = "regular-shopping-basket"
            WHERE `icon_name` = "default-shopping-basket"
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
