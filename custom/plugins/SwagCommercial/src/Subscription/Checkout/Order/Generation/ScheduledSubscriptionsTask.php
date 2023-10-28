<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

#[Package('checkout')]
class ScheduledSubscriptionsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'swag.commercial.subscription.generate.order';
    }

    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}
