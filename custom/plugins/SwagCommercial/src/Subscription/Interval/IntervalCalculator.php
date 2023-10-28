<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Interval;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Interval\Exception\IntervalCalculatorException;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class IntervalCalculator
{
    /**
     * Get a next run date relative to the current date or a specific date.
     */
    public function getNextRunDate(
        SubscriptionIntervalEntity $interval,
        \DateTime|\DateTimeImmutable $date = new \DateTime(),
        int $nth = 0
    ): \DateTime|\DateTimeImmutable {
        return $this->getRunDate($date, $interval, false, $nth);
    }

    /**
     * Get the initial run date relative to the current date or a specific date.
     */
    public function getInitialRunDate(
        SubscriptionIntervalEntity $interval,
        \DateTime|\DateTimeImmutable $date = new \DateTime(),
    ): \DateTime|\DateTimeImmutable {
        return $this->getRunDate($date, $interval, true);
    }

    /**
     * Get multiple run dates starting at the current date or a specific date.
     *
     * @return (\DateTime|\DateTimeImmutable)[]
     */
    public function getMultipleRunDates(
        int $total,
        SubscriptionIntervalEntity $interval,
        bool $startWithInitialDate = true,
        \DateTime|\DateTimeImmutable $date = new \DateTime()
    ): array {
        $matches = [];
        for ($i = 0; $i < $total; ++$i) {
            try {
                $date = $this->getRunDate($date, $interval, $startWithInitialDate);
            } catch (IntervalCalculatorException $e) {
                break;
            }

            $matches[] = $date;
            $startWithInitialDate = false;
        }

        return $matches;
    }

    private function getRunDate(
        \DateTime|\DateTimeImmutable $date,
        SubscriptionIntervalEntity $interval,
        bool $isInitial,
        int $nth = 0
    ): \DateTime|\DateTimeImmutable {
        $date = clone $date;

        for (; $nth >= 0; --$nth) {
            if (!$isInitial) {
                $date = $date->add($interval->getDateInterval());
            }

            try {
                $date = $interval->getCronInterval()->getNextRunDate(
                    $date,
                    0,
                    true,
                    'UTC'
                );
            } catch (\RuntimeException) {
                throw IntervalCalculatorException::cronImpossibleExpression((string) $interval->getCronInterval());
            }

            $isInitial = false;
        }

        return $date;
    }
}
