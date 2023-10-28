<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class MailTemplateTypes
{
    final public const MAILTYPE_ORDER_RETURN_CREATED = 'order_return.created';
    final public const MAILTYPE_ORDER_RETURN_CREATED_MERCHANT = 'order_return.created.merchant';
    final public const MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_IN_PROGRESS = 'order_return.state.in_progress';
    final public const MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_COMPLETED = 'order_return.state.completed';
    final public const MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_CANCELLED = 'order_return.state.cancelled';
}
