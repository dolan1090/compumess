<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\ActivateHandler;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('business-ops')]
class DelayedFlowActionActivateHandler implements ActivateHandler
{
    public function activate(ContainerInterface $container, ActivateContext $context): void
    {
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        // The Delayed Actions have scheduled execution in the de-activate time will expire,
        // it will never be scheduled execute in the future, just execute it manually
        $connection->executeStatement('UPDATE swag_delay_action SET expired = 1 WHERE execution_time < :now', [
            'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
