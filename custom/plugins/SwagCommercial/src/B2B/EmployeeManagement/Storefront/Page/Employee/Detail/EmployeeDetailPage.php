<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\Detail;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Page;

#[Package('checkout')]
class EmployeeDetailPage extends Page
{
    protected ?EmployeeEntity $employee = null;

    protected ?RoleCollection $availableRoles = null;

    protected ?string $defaultRoleId = null;

    public function getEmployee(): ?EmployeeEntity
    {
        return $this->employee;
    }

    public function setEmployee(EmployeeEntity $role): void
    {
        $this->employee = $role;
    }

    public function getAvailableRoles(): ?RoleCollection
    {
        return $this->availableRoles;
    }

    public function setAvailableRoles(RoleCollection $roles): void
    {
        $this->availableRoles = $roles;
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
