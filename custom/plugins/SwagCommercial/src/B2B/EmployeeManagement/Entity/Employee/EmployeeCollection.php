<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<EmployeeEntity>
 */
#[Package('checkout')]
class EmployeeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'employee_collection';
    }

    protected function getExpectedClass(): string
    {
        return EmployeeEntity::class;
    }
}
