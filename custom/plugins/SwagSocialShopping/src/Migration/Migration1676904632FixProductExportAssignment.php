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

class Migration1676904632FixProductExportAssignment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1676904632;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
            UPDATE `product_export` export
            INNER JOIN `swag_social_shopping_sales_channel` soc_sc
                ON export.`sales_channel_id` = soc_sc.`sales_channel_id`
            SET export.`product_stream_id` = soc_sc.`product_stream_id`
            WHERE export.`sales_channel_id` = soc_sc.`sales_channel_id`
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
