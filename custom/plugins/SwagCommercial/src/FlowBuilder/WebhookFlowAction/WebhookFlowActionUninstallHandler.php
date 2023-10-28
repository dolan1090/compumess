<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\WebhookFlowAction;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\WebhookFlowAction\Domain\Action\CallWebhookAction;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('business-ops')]
class WebhookFlowActionUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->delete('flow_sequence', [
            'action_name' => CallWebhookAction::getName(),
        ]);

        $connection->executeStatement('DROP TABLE IF EXISTS `swag_sequence_webhook_event_log`');
    }
}
