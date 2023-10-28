<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('checkout')]
class PermissionEvent extends Struct
{
    /**
     * @param array<string> $permissionDependencies
     */
    public function __construct(
        protected string $permissionName,
        protected array $permissionDependencies,
        protected string $permissionGroupName,
    ) {
    }

    public function getPermissionName(): string
    {
        return $this->permissionName;
    }

    public function setPermissionName(string $permissionName): void
    {
        $this->permissionName = $permissionName;
    }

    /**
     * @return array<string>
     */
    public function getPermissionDependencies(): array
    {
        return $this->permissionDependencies;
    }

    /**
     * @param array<string> $permissionDependencies
     */
    public function setPermissionDependencies(array $permissionDependencies): void
    {
        $this->permissionDependencies = $permissionDependencies;
    }

    public function getPermissionGroupName(): string
    {
        return $this->permissionGroupName;
    }

    public function setPermissionGroupName(string $permissionGroupName): void
    {
        $this->permissionGroupName = $permissionGroupName;
    }
}
