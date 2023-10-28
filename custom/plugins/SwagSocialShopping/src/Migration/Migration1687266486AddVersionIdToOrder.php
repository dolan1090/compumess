<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1687266486AddVersionIdToOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1687266486;
    }

    public function update(Connection $connection): void
    {
        if(!EntityDefinitionQueryHelper::columnExists($connection, 'swag_social_shopping_order', 'version_id')) {
            $connection->executeStatement('ALTER TABLE `swag_social_shopping_order` ADD `version_id` binary(16) DEFAULT 0x0fa91ce3e96a4bc2be4bd9ce752c3425;');
        }

        $connection->executeStatement('ALTER TABLE `swag_social_shopping_order` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `version_id`);');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
