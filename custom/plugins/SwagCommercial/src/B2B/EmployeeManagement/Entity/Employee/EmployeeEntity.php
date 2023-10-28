<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class EmployeeEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected bool $active = true;

    protected string $firstName;

    protected string $lastName;

    protected string $email;

    protected ?string $password = null;

    protected ?RoleEntity $role = null;

    protected ?string $roleId = null;

    protected ?CustomerEntity $businessPartnerCustomer = null;

    protected string $businessPartnerCustomerId;

    protected ?\DateTimeInterface $recoveryTime = null;

    protected ?string $recoveryHash = null;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): ?RoleEntity
    {
        return $this->role;
    }

    public function setRole(?RoleEntity $role): void
    {
        $this->role = $role;
    }

    public function getRoleId(): ?string
    {
        return $this->roleId;
    }

    public function setRoleId(?string $roleId): void
    {
        $this->roleId = $roleId;
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

    public function getRecoveryTime(): ?\DateTimeInterface
    {
        return $this->recoveryTime;
    }

    public function setRecoveryTime(?\DateTimeInterface $recoveryTime): void
    {
        $this->recoveryTime = $recoveryTime;
    }

    public function getRecoveryHash(): ?string
    {
        return $this->recoveryHash;
    }

    public function setRecoveryHash(?string $recoveryHash): void
    {
        $this->recoveryHash = $recoveryHash;
    }
}
