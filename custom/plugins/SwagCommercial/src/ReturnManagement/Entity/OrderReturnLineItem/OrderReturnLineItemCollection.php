<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderReturnLineItemEntity>
 */
#[Package('checkout')]
class OrderReturnLineItemCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_return_line_item_collection';
    }

    /**
     * @param string[] $states
     */
    public function getByStates(array $states): OrderReturnLineItemCollection
    {
        return $this->filter(fn (OrderReturnLineItemEntity $orderReturnLineItem) => $orderReturnLineItem->getState() !== null && \in_array($orderReturnLineItem->getState()->getTechnicalName(), $states, true));
    }

    protected function getExpectedClass(): string
    {
        return OrderReturnLineItemEntity::class;
    }
}
