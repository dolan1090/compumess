<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturn;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderReturnEntity>
 */
#[Package('checkout')]
class OrderReturnCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_return_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderReturnEntity::class;
    }
}
