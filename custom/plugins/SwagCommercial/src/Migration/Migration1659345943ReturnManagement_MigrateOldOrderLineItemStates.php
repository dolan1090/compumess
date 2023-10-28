<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1659345943ReturnManagement_MigrateOldOrderLineItemStates extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1659345943;
    }

    public function update(Connection $connection): void
    {
        $deliveryStates = $this->getStateMachineStateIds(OrderDeliveryStates::STATE_MACHINE, [
            OrderDeliveryStates::STATE_OPEN,
            OrderDeliveryStates::STATE_CANCELLED,
            OrderDeliveryStates::STATE_SHIPPED,
            OrderDeliveryStates::STATE_PARTIALLY_SHIPPED,
            OrderDeliveryStates::STATE_RETURNED,
            OrderDeliveryStates::STATE_PARTIALLY_RETURNED,
        ], $connection, 'delivery_');

        $lineItemStates = $this->getStateMachineStateIds(PositionStateHandler::STATE_MACHINE, [
            PositionStateHandler::STATE_OPEN,
            PositionStateHandler::STATE_CANCELLED,
            PositionStateHandler::STATE_SHIPPED,
            PositionStateHandler::STATE_SHIPPED_PARTIALLY,
            PositionStateHandler::STATE_RETURNED,
            PositionStateHandler::STATE_RETURNED_PARTIALLY,
        ], $connection, 'line_item_');

        $this->updateOrderLineItemStatesBasedOnDeliveryStates($lineItemStates, $deliveryStates, $connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param array<string, mixed> $orderLineItemStates
     * @param array<string, mixed> $deliveryStates
     */
    private function updateOrderLineItemStatesBasedOnDeliveryStates(array $orderLineItemStates, array $deliveryStates, Connection $connection): void
    {
        $sql = <<< SQL
        UPDATE `order_line_item` INNER JOIN `order_delivery`
        ON `order_line_item`.`order_id` = `order_delivery`.`order_id`
        SET
            `order_line_item`.`state_id` = CASE
                WHEN `order_delivery`.`state_id` = :delivery_open THEN :line_item_open
                WHEN `order_delivery`.`state_id` = :delivery_cancelled THEN :line_item_cancelled
                WHEN `order_delivery`.`state_id` = :delivery_shipped THEN :line_item_shipped
                WHEN `order_delivery`.`state_id` = :delivery_shipped_partially THEN :line_item_shipped_partially
                WHEN `order_delivery`.`state_id` = :delivery_returned THEN :line_item_returned
                WHEN `order_delivery`.`state_id` = :delivery_returned_partially THEN :line_item_returned_partially
            END,
            `order_line_item`.`updated_at` = :updated_at
        WHERE `order_delivery`.`state_id` IN (:delivery_states);
SQL;
        $connection->executeStatement(
            $sql,
            array_merge(
                $orderLineItemStates,
                $deliveryStates,
                [
                    'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'delivery_states' => array_values($deliveryStates),
                ]
            ),
            [
                'delivery_states' => ArrayParameterType::STRING,
            ]
        );
    }

    /**
     * @param array<int, string> $stateTechnicalNames
     *
     * @return array<string, mixed>
     */
    private function getStateMachineStateIds(string $machineTechnicalName, array $stateTechnicalNames, Connection $connection, string $prefixKey = ''): array
    {
        return array_merge(...array_map(
            fn ($row) => [$prefixKey . $row['technical_name'] => $row['state_id']],
            $connection->fetchAllAssociative(
                'SELECT `state_machine_state`.`technical_name`, `state_machine_state`.`id` `state_id`
                        FROM `state_machine_state` JOIN `state_machine`
                            ON `state_machine_state`.`state_machine_id` = `state_machine`.`id`
                        WHERE
                            `state_machine`.`technical_name` = :machine_technical_name AND `state_machine_state`.`technical_name` IN (:technical_names)',
                [
                    'machine_technical_name' => $machineTechnicalName,
                    'technical_names' => $stateTechnicalNames,
                ],
                [
                    'technical_names' => ArrayParameterType::STRING,
                ]
            )
        ));
    }
}
