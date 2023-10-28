<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('inventory')]
class MultiWarehouseUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        $this->dropMultiWarehouseTables($container->get(Connection::class));
    }

    private function dropMultiWarehouseTables(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS
    `order_warehouse_group`,
    `order_product_warehouse`,
    `product_warehouse_group`,
    `product_warehouse`,
    `warehouse_group_warehouse`,
    `warehouse_group`,
    `warehouse`;

DELETE FROM migration WHERE `class` = "%Migration1658302919AddMultiWarehouseEntities";
        ');
    }
}
