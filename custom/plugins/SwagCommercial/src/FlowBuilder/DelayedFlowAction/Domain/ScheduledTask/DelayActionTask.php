<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\ScheduledTask;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('business-ops')]
class DelayActionTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag_delay_action.execute';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 24 hours
    }
}
