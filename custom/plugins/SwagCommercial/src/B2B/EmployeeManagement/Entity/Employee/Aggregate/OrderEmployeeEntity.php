<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\Aggregate;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderEmployeeEntity extends Entity
{
    protected ?OrderEntity $order = null;

    protected ?EmployeeEntity $employee = null;

    protected string $firstName;

    protected string $lastName;

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getEmployee(): ?EmployeeEntity
    {
        return $this->employee;
    }

    public function setEmployee(?EmployeeEntity $employee): void
    {
        $this->employee = $employee;
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
}
