<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\Detail;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollection;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Page;

#[Package('checkout')]
class RoleDetailPage extends Page
{
    protected ?RoleEntity $role = null;

    protected PermissionEventCollection $permissions;

    protected ?string $defaultRoleId = null;

    public function getRole(): ?RoleEntity
    {
        return $this->role;
    }

    public function setRole(RoleEntity $role): void
    {
        $this->role = $role;
    }

    public function getPermissions(): PermissionEventCollection
    {
        return $this->permissions;
    }

    public function setPermissions(PermissionEventCollection $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function getDefaultRoleId(): ?string
    {
        return $this->defaultRoleId;
    }

    public function setDefaultRoleId(?string $defaultRoleId): void
    {
        $this->defaultRoleId = $defaultRoleId;
    }
}
