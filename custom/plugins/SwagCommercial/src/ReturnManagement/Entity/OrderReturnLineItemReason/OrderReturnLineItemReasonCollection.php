<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderReturnLineItemReasonEntity>
 */
#[Package('checkout')]
class OrderReturnLineItemReasonCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_return_line_item_reason_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderReturnLineItemReasonEntity::class;
    }
}
