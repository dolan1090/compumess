<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Reporting;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class TurnoverCollector
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, array<string, array<string, array<string, float|int>|float>>>
     */
    public function collect(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $stmt = $this->connection->prepare('
            SELECT ROUND(SUM(`order`.`amount_total`), 2) AS `turnover_total`,
                   ROUND(SUM(`order`.`amount_net`), 2) AS `turnover_net`,
                   COUNT(`order`.`id`) AS `order_count`,
                   `order`.`order_date` AS `date`,
                   `currency`.`iso_code` AS `currency_iso_code`,
                   `currency`.`factor` AS `currency_factor`
            FROM `order`
            INNER JOIN `currency` on `order`.currency_id = `currency`.`id`
            WHERE `order`.`order_date` BETWEEN :start AND :end
                AND `order`.`version_id` = :liveVersionId
                AND (JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") IS NULL OR JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") = 0)
            GROUP BY `order`.`order_date`,
                     `order`.`currency_id`
        ');

        $result = $stmt->executeQuery([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'liveVersionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
        ]);

        $orderLineItemCounts = $this->fetchOrderLineItemCounts($start, $end);

        $records = [];

        while (($row = $result->fetchAssociative()) !== false) {
            /** @var array<string, string> $row */
            $date = $row['date'];

            /** @var string $currencyIsoCode */
            $currencyIsoCode = $row['currency_iso_code'];

            $orderLineItemCount = $orderLineItemCounts[$date][$currencyIsoCode] ?? 0.0;

            $records[$date][$currencyIsoCode] = [
                'amountTotal' => (float) $row['turnover_total'],
                'amountNet' => (float) $row['turnover_net'],
                'orderCount' => (int) $row['order_count'],
                'currencyFactor' => (float) $row['currency_factor'],
                'itemsPerOrder' => round($orderLineItemCount / (int) $row['order_count'], 2),
            ];
        }

        return $records;
    }

    /**
     * @return array<string, array<string, float>>
     */
    private function fetchOrderLineItemCounts(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $items = $this->connection->prepare('
            SELECT `order`.`order_date`,
                   `currency`.`iso_code` AS `currency_iso_code`,
                    COUNT(`order_line_item`.`id`) AS `line_item_count`
            FROM `order`
                INNER JOIN `order_line_item` ON `order`.id = `order_line_item`.`order_id`
                INNER JOIN `currency` on `order`.`currency_id` = `currency`.`id`
            WHERE `order`.`order_date` BETWEEN :start AND :end
                AND `order`.`version_id` = :liveVersionId
                AND (JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") IS NULL OR JSON_CONTAINS(`order`.`custom_fields`, "true", "$.saas_test_order") = 0)
                AND order_line_item.good = 1
                AND order_line_item.parent_id IS NULL
            GROUP BY `order`.`order_date`,
                     `order`.`currency_id`
        ')->executeQuery([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'liveVersionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
        ])->fetchAllAssociative();

        $orderLineItemCounts = [];

        /** @var array<string, string> $item */
        foreach ($items as $item) {
            $date = $item['order_date'];
            $lineItemCount = $item['line_item_count'];
            $currencyIsoCode = $item['currency_iso_code'];

            $orderLineItemCounts[$date][$currencyIsoCode] = (float) $lineItemCount;
        }

        return $orderLineItemCounts;
    }
}
