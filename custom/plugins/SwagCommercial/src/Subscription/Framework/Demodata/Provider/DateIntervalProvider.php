<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Provider;

use Faker\Provider\Base;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class DateIntervalProvider extends Base
{
    public function dateInterval(): DateInterval
    {
        $string = \sprintf(
            'P%s%s',
            $this->dateIntervalInterval(),
            $this->dateIntervalUnit(),
        );

        return new DateInterval($string);
    }

    private function dateIntervalUnit(): string
    {
        /** @var string $type */
        $type = $this->generator->randomElement(['D', 'W', 'M']);

        return $type;
    }

    private function dateIntervalInterval(): string
    {
        return (string) $this->generator->numberBetween(1, 30);
    }
}
