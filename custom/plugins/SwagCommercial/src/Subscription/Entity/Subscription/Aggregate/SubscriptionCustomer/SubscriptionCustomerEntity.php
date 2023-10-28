<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionCustomer;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Salutation\SalutationEntity;

#[Package('checkout')]
class SubscriptionCustomerEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $email;

    protected string $subscriptionId;

    protected ?string $salutationId = null;

    protected string $firstName;

    protected string $lastName;

    protected ?string $title = null;

    /**
     * @var string[]|null
     */
    protected ?array $vatIds = null;

    protected ?string $company = null;

    protected ?string $customerNumber = null;

    protected ?string $customerId = null;

    protected ?CustomerEntity $customer = null;

    protected ?SalutationEntity $salutation = null;

    protected ?SubscriptionEntity $subscription = null;

    protected ?string $remoteAddress = null;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(string $subscriptionId): void
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function getSalutationId(): ?string
    {
        return $this->salutationId;
    }

    public function setSalutationId(?string $salutationId): void
    {
        $this->salutationId = $salutationId;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string[]|null
     */
    public function getVatIds(): ?array
    {
        return $this->vatIds;
    }

    /**
     * @param string[]|null $vatIds
     */
    public function setVatIds(?array $vatIds): void
    {
        $this->vatIds = $vatIds;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    public function getCustomerNumber(): ?string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(?string $customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getSalutation(): ?SalutationEntity
    {
        return $this->salutation;
    }

    public function setSalutation(?SalutationEntity $salutation): void
    {
        $this->salutation = $salutation;
    }

    public function getSubscription(): ?SubscriptionEntity
    {
        return $this->subscription;
    }

    public function setSubscription(?SubscriptionEntity $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function getRemoteAddress(): ?string
    {
        return $this->remoteAddress;
    }

    public function setRemoteAddress(?string $remoteAddress): void
    {
        $this->remoteAddress = $remoteAddress;
    }
}
