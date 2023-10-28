<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Entity;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSpecificFeaturesEntity extends Entity
{
    use EntityIdTrait;

    protected string $customerId;

    /**
     * @var array<string, bool>
     */
    protected array $features = [];

    protected ?CustomerEntity $customer;

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return array<string, bool>
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * @param array<string, bool> $features
     */
    public function setFeatures(array $features): void
    {
        $this->features = $features;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }
}
