<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
final class BaseEmployeePermissions
{
    public const EMPLOYEE_READ = 'employee.read';

    public const EMPLOYEE_EDIT = 'employee.edit';

    public const EMPLOYEE_CREATE = 'employee.create';

    public const EMPLOYEE_DELETE = 'employee.delete';

    public const ROLE_READ = 'role.read';

    public const ROLE_EDIT = 'role.edit';

    public const ROLE_CREATE = 'role.create';

    public const ROLE_DELETE = 'role.delete';

    public const ORDER_READ_ALL = 'order.read.all';

    public static function getBaseCollection(): PermissionEventCollection
    {
        $permissions = [
            self::EMPLOYEE_READ => [
                'group' => BasePermissionGroups::EMPLOYEE->value,
                'dependencies' => [],
            ],
            self::EMPLOYEE_EDIT => [
                'group' => BasePermissionGroups::EMPLOYEE->value,
                'dependencies' => [
                    self::EMPLOYEE_READ,
                ],
            ],
            self::EMPLOYEE_CREATE => [
                'group' => BasePermissionGroups::EMPLOYEE->value,
                'dependencies' => [
                    self::EMPLOYEE_READ,
                    self::EMPLOYEE_EDIT,
                ],
            ],
            self::EMPLOYEE_DELETE => [
                'group' => BasePermissionGroups::EMPLOYEE->value,
                'dependencies' => [
                    self::EMPLOYEE_READ,
                    self::EMPLOYEE_EDIT,
                ],
            ],
            self::ROLE_READ => [
                'group' => BasePermissionGroups::ROLE->value,
                'dependencies' => [],
            ],
            self::ROLE_EDIT => [
                'group' => BasePermissionGroups::ROLE->value,
                'dependencies' => [
                    self::ROLE_READ,
                ],
            ],
            self::ROLE_CREATE => [
                'group' => BasePermissionGroups::ROLE->value,
                'dependencies' => [
                    self::ROLE_READ,
                    self::ROLE_EDIT,
                ],
            ],
            self::ROLE_DELETE => [
                'group' => BasePermissionGroups::ROLE->value,
                'dependencies' => [
                    self::ROLE_READ,
                    self::ROLE_EDIT,
                ],
            ],
            self::ORDER_READ_ALL => [
                'group' => BasePermissionGroups::ORDER->value,
                'dependencies' => [],
            ],
        ];

        $collection = new PermissionEventCollection();

        foreach ($permissions as $name => $permission) {
            $entity = new PermissionEvent($name, $permission['dependencies'], $permission['group']);
            $collection->add($entity);
        }

        return $collection;
    }
}
