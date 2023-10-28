<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Provider;

use Faker\Provider\Base;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class CronIntervalProvider extends Base
{
    public function cron(): CronInterval
    {
        $string = \sprintf(
            '* * %s %s %s',
            $this->cronDayOfMonth(),
            $this->cronMonth(),
            $this->cronDayOfWeek()
        );

        return new CronInterval($string);
    }

    private function cronDayOfMonth(): string
    {
        if ($this->generator->boolean()) {
            return '*';
        }

        $day = $this->generator->numberBetween(1, 30);

        if ($day < 28 && $this->generator->boolean(30)) {
            $toDay = $this->numberBetween($day + 1, 30);

            for ($i = $day + 1; $i < $toDay; ++$i) {
                $day .= ',' . $i;
            }
        }

        return (string) $day;
    }

    private function cronMonth(): string
    {
        if ($this->generator->boolean()) {
            return '*';
        }

        $month = $this->generator->numberBetween(1, 12);

        if ($month < 11 && $this->generator->boolean(30)) {
            $toMonth = $this->numberBetween($month + 1, 12);

            for ($i = $month + 1; $i < $toMonth; ++$i) {
                $month .= ',' . $i;
            }
        }

        return (string) $month;
    }

    private function cronDayOfWeek(): string
    {
        if ($this->generator->boolean()) {
            return '*';
        }

        $day = $this->generator->numberBetween(0, 6);

        if ($day < 5 && $this->generator->boolean(30)) {
            $toDay = $this->numberBetween($day + 1, 6);

            for ($i = $day + 1; $i < $toDay; ++$i) {
                $day .= ',' . $i;
            }
        }

        return (string) $day;
    }
}
