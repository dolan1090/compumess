<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Flag;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
interface EmployeeAware
{
    public const EMPLOYEE_ID = 'employeeId';

    public const EMPLOYEE = 'employee';

    public function getEmployeeId(): string;
}
