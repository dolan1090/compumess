<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Reporting;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class OrderStatusChangedCollector
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, array<string, array{amountTotal: float, amountNet: float, orderCount: int, currencyFactor: float }>>
     */
    public function collectCancelledOrders(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->collect($start, $end, 'to_state_id');
    }

    /**
     * @return array<string, array<string, array{amountTotal: float, amountNet: float, orderCount: int, currencyFactor: float }>>
     */
    public function collectReopenedOrders(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->collect($start, $end, 'from_state_id');
    }

    /**
     * @return array<string, array<string, array{amountTotal: float, amountNet: float, orderCount: int, currencyFactor: float }>>
     */
    private function collect(\DateTimeImmutable $start, \DateTimeImmutable $end, string $column): array
    {
        $orderStateId = $this->getCancelledOrderStateId();

        $query = sprintf('
            SELECT
                ROUND(SUM(`order`.`amount_total`), 2) AS `amount_total`,
                ROUND(SUM(`order`.`amount_net`), 2) AS `amount_net`,
                COUNT(`order`.`id`) AS `order_count`,
                `order`.`order_date` AS `order_date`,
                CAST(`order_state`.`created_at` AS DATE) AS `date`,
                `currency`.`iso_code` AS `currency_iso_code`,
                `currency`.`factor` AS currency_factor
            FROM `order`
            INNER JOIN `currency` on `currency`.`id` = `order`.`currency_id`
            INNER JOIN state_machine_history AS `order_state`
                ON UNHEX(JSON_UNQUOTE(JSON_EXTRACT(`order_state`.`entity_id`, "$.id"))) = `order`.`id`
                AND UNHEX(JSON_UNQUOTE(JSON_EXTRACT(`order_state`.`entity_id`, "$.version_id"))) = `order`.`version_id`
                AND `order_state`.`entity_name` = "order"
                AND CAST(`order_state`.`created_at` AS DATE) BETWEEN :start AND :end
                AND (`order_state`.`%s` = :stateId)
            WHERE `order`.`version_id` = :liveVersionId
                AND (JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") IS NULL OR JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") = 0)
            GROUP BY
                `date`,
                `order`.`currency_id`;
        ', $column);

        $result = $this->connection
            ->prepare($query)
            ->executeQuery([
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'stateId' => $orderStateId,
                'liveVersionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            ]);

        $records = [];

        while (($row = $result->fetchAssociative()) !== false) {
            /** @var array<string, string> $row */
            $date = $row['date'];
            $currencyIsoCode = $row['currency_iso_code'];

            $records[$date][$currencyIsoCode] = [
                'amountTotal' => (float) $row['amount_total'],
                'amountNet' => (float) $row['amount_net'],
                'orderCount' => (int) $row['order_count'],
                'currencyFactor' => (float) $row['currency_factor'],
            ];
        }

        return $records;
    }

    private function getCancelledOrderStateId(): string
    {
        $result = $this->connection->prepare('
            SELECT `state_machine_state`.`id` AS `state_id`
            FROM `state_machine_state`
              INNER JOIN `state_machine` ON `state_machine`.id = `state_machine_state`.`state_machine_id`
            WHERE `state_machine`.`technical_name` = :stateMachineName
              AND `state_machine_state`.`technical_name` = :stateMachineStateName
        ')->executeQuery([
            'stateMachineName' => OrderStates::STATE_MACHINE,
            'stateMachineStateName' => OrderStates::STATE_CANCELLED,
        ]);

        $orderStateId = $result->fetchOne();

        if (!\is_string($orderStateId)) {
            throw new \RuntimeException(sprintf('Id for order state "%s" not found', OrderStates::STATE_CANCELLED));
        }

        return $orderStateId;
    }
}
