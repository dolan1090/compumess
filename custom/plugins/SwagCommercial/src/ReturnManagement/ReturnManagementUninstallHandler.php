<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('checkout')]
class ReturnManagementUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $this->deleteReturnRecords($connection);
        $this->dropReturnManagementTables($connection);
        $this->dropReturnFlows($connection);
    }

    private function deleteReturnRecords(Connection $connection): void
    {
        // Delete state_machine_history records
        $connection->executeStatement(
            'DELETE `state_machine_history`
FROM `state_machine_history`
INNER JOIN `state_machine` ON `state_machine_history`.state_machine_id = `state_machine`.id
WHERE `state_machine`.`technical_name` = :technical_name',
            [
                'technical_name' => 'order_return.state',
            ]
        );

        // Delete state_machine_transition records
        $connection->executeStatement(
            'DELETE `state_machine_transition`
FROM `state_machine_transition`
INNER JOIN `state_machine` ON `state_machine_transition`.state_machine_id = `state_machine`.id
WHERE `state_machine`.technical_name = :technical_name',
            [
                'technical_name' => 'order_return.state',
            ]
        );

        // Drop state_id column in order_line_item
        $connection->executeStatement('ALTER TABLE `order_line_item`
DROP FOREIGN KEY `fk.order_line_item.state_id`,
DROP COLUMN `state_id`');

        // Drop state_id column in order_return_line_item
        $connection->executeStatement('ALTER TABLE `order_return_line_item`
DROP FOREIGN KEY `fk.order_return_line_item.state_id`,
DROP COLUMN `state_id`');

        // Delete state_machine_state records
        $connection->executeStatement(
            'DELETE `state_machine_state`
FROM `state_machine_state`
INNER JOIN `state_machine` ON `state_machine_state`.state_machine_id = `state_machine`.id
WHERE `state_machine`.technical_name in (:technical_names)',
            [
                'technical_names' => ['order_return.state', 'order_line_item.state'],
            ],
            [
                'technical_names' => ArrayParameterType::STRING,
            ]
        );

        // Delete state_machine records
        $connection->executeStatement(
            'DELETE FROM `state_machine`
WHERE `state_machine`.technical_name in (:technical_names)',
            [
                'technical_names' => ['order_return.state', 'order_line_item.state'],
            ],
            [
                'technical_names' => ArrayParameterType::STRING,
            ]
        );
    }

    private function dropReturnManagementTables(Connection $connection): void
    {
        $connection->executeStatement(
            'DROP TABLE IF EXISTS
    `order_return_line_item`,
    `order_return_line_item_reason_translation`,
    `order_return_line_item_reason`,
    `order_return`;

DELETE FROM `migration` WHERE `class` like :modules_name;',
            [
                'modules_name' => '%ReturnManagement%',
            ]
        );
    }

    private function dropReturnFlows(Connection $connection): void
    {
        $listEventName = [
            'checkout.order.return.created' => 'Order return created',
            'state_enter.order_return.state.in_progress' => 'Order return enters status in progress',
            'state_enter.order_return.state.cancelled' => 'Order return enters status cancelled',
            'state_enter.order_return.state.done' => 'Order return enters status completed',
        ];

        $connection->executeStatement(
            'DELETE FROM `flow`
                    WHERE `event_name` in (:event_names)',
            [
                'event_names' => array_keys($listEventName),
            ],
            [
                'event_names' => ArrayParameterType::STRING,
            ]
        );

        $connection->executeStatement(
            'DELETE FROM `flow_template`
                    WHERE `name` in (:names)',
            [
                'names' => array_values($listEventName),
            ],
            [
                'names' => ArrayParameterType::STRING,
            ]
        );
    }
}
