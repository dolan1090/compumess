<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturn;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class OrderReturnStates
{
    public const STATE_MACHINE = 'order_return.state';
    public const STATE_OPEN = 'open';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_DONE = 'done';
    public const STATE_CANCELLED = 'cancelled';
}
