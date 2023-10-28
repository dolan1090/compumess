<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class BusinessPartnerEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected ?CustomerEntity $customer;

    protected ?RoleEntity $defaultRole;

    protected string $customerId;

    protected ?string $defaultRoleId = null;

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customerEntity): void
    {
        $this->customer = $customerEntity;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getDefaultRole(): ?RoleEntity
    {
        return $this->defaultRole;
    }

    public function setDefaultRole(?RoleEntity $defaultRole): void
    {
        $this->defaultRole = $defaultRole;
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
