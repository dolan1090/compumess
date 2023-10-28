<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CleanVersionsTask extends ScheduledTask
{
    private const TIME_INTERVAL_DAILY = 86400;

    public static function getTaskName(): string
    {
        return 'swag_customized_product.clean_versions';
    }

    public static function getDefaultInterval(): int
    {
        return self::TIME_INTERVAL_DAILY;
    }
}
