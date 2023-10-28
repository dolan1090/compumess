<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class OrderReturnLineItemStates
{
    public const STATE_MACHINE = 'order_return_line_item.state';
    public const STATE_RETURN_REQUESTED = 'return_requested';
    public const STATE_RETURNED = 'returned';
    public const STATE_RETURNED_PARTIALLY = 'returned_partially';
    public const STATE_CANCELLED = 'cancelled';
}
