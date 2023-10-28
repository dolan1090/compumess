<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\ScheduledTask;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * @internal
 */
#[Package('merchant-services')]
final class UpdateCommercialLicenseTask extends ScheduledTask
{
    public const ONE_DAY = 86400;

    public static function getTaskName(): string
    {
        return 'swag.commercial.update_license';
    }

    public static function getDefaultInterval(): int
    {
        return self::ONE_DAY;
    }
}
