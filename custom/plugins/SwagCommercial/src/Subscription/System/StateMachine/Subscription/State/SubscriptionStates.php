<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\System\StateMachine\Subscription\State;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class SubscriptionStates
{
    public const STATE_MACHINE = 'subscription.state';
    public const STATE_ACTIVE = 'active';
    public const STATE_PAUSED = 'paused';
    public const STATE_CANCELLED = 'cancelled';
    public const STATE_FLAGGED_CANCELLED = 'flagged_cancelled';
    public const STATE_PAYMENT_FAILED = 'payment_failed';

    /**
     * @codeCoverageIgnore private constructor
     */
    private function __construct()
    {
    }
}
