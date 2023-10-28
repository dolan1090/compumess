<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
enum BasePermissionGroups: string
{
    case EMPLOYEE = 'employee';
    case ROLE = 'role';
    case ORDER = 'order';
}
