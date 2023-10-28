<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\System\StateMachine\Subscription\State;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class SubscriptionStateActions
{
    public const ACTION_ACTIVATE = 'activate';
    public const ACTION_PAUSE = 'pause';
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_FLAG_FOR_CANCELLATION = 'flag_for_cancellation';
    public const ACTION_FAIL_PAYMENT = 'fail_payment';

    /**
     * @codeCoverageIgnore private constructor
     */
    private function __construct()
    {
    }
}
