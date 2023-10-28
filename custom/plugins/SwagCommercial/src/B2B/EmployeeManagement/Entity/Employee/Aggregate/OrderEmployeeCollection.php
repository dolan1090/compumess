<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderEmployeeEntity>
 */
#[Package('checkout')]
class OrderEmployeeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_employee_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderEmployeeEntity::class;
    }
}
