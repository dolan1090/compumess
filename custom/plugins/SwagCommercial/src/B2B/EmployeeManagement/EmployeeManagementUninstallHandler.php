<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('checkout')]
class EmployeeManagementUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        $this->dropB2BTables($container->get(Connection::class));
    }

    private function dropB2BTables(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            DROP TABLE IF EXISTS `b2b_order_employee`;
            DELETE FROM migration WHERE `class` = "%Migration1684762299CreateB2BOrderEmployeeTable";

            DROP TABLE IF EXISTS `b2b_employee`;
            DELETE FROM migration WHERE `class` = "%Migration1679614358CreateB2BEmployeeTable";

            DROP TABLE IF EXISTS `b2b_business_partner`;
            DELETE FROM migration WHERE `class` = "%Migration1682322727CreateB2BBusinessPartnerTable";

            DROP TABLE IF EXISTS `b2b_components_role`;
            DELETE FROM migration WHERE `class` = "%Migration1684755900AddRoleEntity";

            DROP TABLE IF EXISTS `b2b_permission`;
            DELETE FROM migration WHERE `class` = "%Migration1684755900AddPermissionEntity";

            DELETE FROM system_config WHERE `configuration_key` = "b2b.employee.invitationURL";
        SQL);
    }
}
