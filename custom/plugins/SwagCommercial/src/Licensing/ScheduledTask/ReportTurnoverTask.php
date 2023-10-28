<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\ScheduledTask;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * @internal
 */
#[Package('merchant-services')]
final class ReportTurnoverTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag.commercial.report_turnover';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 1 day
    }
}
