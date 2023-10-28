<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionUninstallHandler implements UninstallHandler
{
    // removal order
    public const SUBSCRIPTION_TABLES = [
        'subscription_address',
        'subscription_customer',
        'subscription_cart',
        'subscription_plan_interval_mapping',
        'subscription_interval_translation',
        'subscription_plan_product_mapping',
        'subscription_plan_translation',
        'subscription_tag_mapping',
        'subscription',
        'subscription_interval',
        'subscription_plan',
    ];

    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $this->dropOrderSubscriptionForeignKey($connection);
        $this->dropTables($connection);
        $this->dropStateMachine($connection);
        $this->dropNumberRange($connection);
    }

    private function dropOrderSubscriptionForeignKey(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $fks = $manager->listTableForeignKeys(OrderDefinition::ENTITY_NAME);
        $fks = \array_map(static fn (ForeignKeyConstraint $fk) => $fk->getName(), $fks);

        if (\in_array('fk.order.subscription_id', $fks, true)) {
            $connection->executeStatement('ALTER TABLE `order` DROP FOREIGN KEY `fk.order.subscription_id`');
        }

        $columns = $manager->listTableColumns(OrderDefinition::ENTITY_NAME);

        if (\array_key_exists('subscription_id', $columns)) {
            $connection->executeStatement('ALTER TABLE `order` DROP COLUMN `subscription_id`');
        }
    }

    private function dropTables(Connection $connection): void
    {
        $sql = 'DROP TABLE IF EXISTS `#table#`';

        foreach (self::SUBSCRIPTION_TABLES as $table) {
            $exec = \str_replace('#table#', $table, $sql);
            $connection->executeStatement($exec);
        }
    }

    private function dropStateMachine(Connection $connection): void
    {
        $stateMachine = SubscriptionStates::STATE_MACHINE;
        $stateMachineId = $connection->fetchOne('SELECT id FROM `state_machine` WHERE `technical_name` = :name', ['name' => $stateMachine]);

        if (!$stateMachineId) {
            return;
        }

        $connection->executeStatement('DELETE FROM `state_machine_transition` WHERE `state_machine_id` = :id', ['id' => $stateMachineId]);
        $connection->executeStatement('DELETE FROM `state_machine_translation` WHERE `state_machine_id` = :id', ['id' => $stateMachineId]);
        $connection->executeStatement('DELETE FROM `state_machine_state` WHERE `state_machine_id` = :id', ['id' => $stateMachineId]);
        $connection->executeStatement('DELETE FROM `state_machine` WHERE `id` = :id', ['id' => $stateMachineId]);
    }

    private function dropNumberRange(Connection $connection): void
    {
        $numberRangeType = $connection->fetchOne('SELECT id FROM `number_range_type` WHERE `technical_name` = :name', ['name' => 'subscription']);

        if (!$numberRangeType) {
            return;
        }

        $numberRange = $connection->fetchOne('SELECT id FROM `number_range` WHERE `type_id` = :type', ['type' => $numberRangeType]);

        if ($numberRange) {
            $connection->executeStatement('DELETE FROM `number_range_translation` WHERE `number_range_id` = :id', ['id' => $numberRange]);
            $connection->executeStatement('DELETE FROM `number_range` WHERE `id` = :id', ['id' => $numberRange]);
        }

        $connection->executeStatement('DELETE FROM `number_range_type_translation` WHERE `number_range_type_id` = :id', ['id' => $numberRangeType]);
        $connection->executeStatement('DELETE FROM `number_range_type` WHERE `id` = :id', ['id' => $numberRangeType]);
    }
}
