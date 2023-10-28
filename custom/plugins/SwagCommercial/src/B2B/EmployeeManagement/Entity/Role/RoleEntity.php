<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Role;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class RoleEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $name;

    protected ?CustomerEntity $businessPartnerCustomer = null;

    protected string $businessPartnerCustomerId;

    protected ?EmployeeCollection $employees = null;

    /**
     * @var array<string>
     */
    protected ?array $permissions = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBusinessPartnerCustomer(): ?CustomerEntity
    {
        return $this->businessPartnerCustomer;
    }

    public function setBusinessPartnerCustomer(CustomerEntity $businessPartnerCustomer): void
    {
        $this->businessPartnerCustomer = $businessPartnerCustomer;
    }

    public function getBusinessPartnerCustomerId(): string
    {
        return $this->businessPartnerCustomerId;
    }

    public function setBusinessPartnerCustomerId(string $businessPartnerCustomerId): void
    {
        $this->businessPartnerCustomerId = $businessPartnerCustomerId;
    }

    public function getEmployees(): ?EmployeeCollection
    {
        return $this->employees;
    }

    public function setEmployees(?EmployeeCollection $employees): void
    {
        $this->employees = $employees;
    }

    /**
     * @return string[]|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * @param array<string>|null $permissions
     */
    public function setPermissions(?array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @param string ...$permissions
     */
    public function can(...$permissions): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return empty(array_diff($permissions, $this->permissions));
    }
}
