<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\List;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Page;

#[Package('checkout')]
class RoleListPage extends Page
{
    protected ?EntitySearchResult $roles = null;

    protected ?string $defaultRoleId = null;

    public function getRoles(): ?EntitySearchResult
    {
        return $this->roles;
    }

    public function setRoles(EntitySearchResult $roles): void
    {
        $this->roles = $roles;
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
