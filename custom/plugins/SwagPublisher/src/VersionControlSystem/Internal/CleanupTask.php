<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CleanupTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag.publisher_version_control_cleanup';
    }

    public static function getDefaultInterval(): int
    {
        // 24 Hours
        return 86400;
    }
}
