<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Reporting;

use Shopware\Core\Framework\Log\Package;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class TurnoverReporter
{
    final public const VERSION = 1;

    public function __construct(
        private readonly TurnoverCollector $turnoverCollector,
        private readonly OrderStatusChangedCollector $orderStatusChangedCollector,
        private readonly DefaultCurrencyCollector $defaultCurrencyCollector
    ) {
    }

    /**
     * @return array{version: int, turnover: array<string, mixed>, canceled: array<string, mixed>, reopened: array<string, mixed>, defaultCurrency: array<string, mixed>}
     */
    public function collect(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return [
            'version' => self::VERSION,
            'turnover' => $this->turnoverCollector->collect($start, $end),
            'canceled' => $this->orderStatusChangedCollector->collectCancelledOrders($start, $end),
            'reopened' => $this->orderStatusChangedCollector->collectReopenedOrders($start, $end),
            'defaultCurrency' => $this->defaultCurrencyCollector->collect(),
        ];
    }
}
