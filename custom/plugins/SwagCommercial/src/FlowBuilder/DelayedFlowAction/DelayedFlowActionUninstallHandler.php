<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('business-ops')]
class DelayedFlowActionUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->delete('flow_sequence', [
            'action_name' => 'action.delay',
        ]);

        $connection->executeStatement('DROP TABLE IF EXISTS `swag_delay_action`');
    }
}
