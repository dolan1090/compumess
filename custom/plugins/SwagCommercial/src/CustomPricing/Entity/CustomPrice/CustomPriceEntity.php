<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\CustomPrice;

use Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price\CustomPriceCollection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class CustomPriceEntity extends Entity
{
    use EntityIdTrait;

    protected ?ProductEntity $product = null;

    protected string $productId;

    protected ?CustomerEntity $customer = null;

    protected string $customerId;

    protected ?string $customerGroupId = null;

    protected ?CustomerGroupEntity $customerGroup = null;

    /**
     * @var array<CustomPriceCollection>
     */
    protected array $price = [];

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return array<CustomPriceCollection>
     */
    public function getPrice(): array
    {
        return $this->price;
    }

    /**
     * @param array<CustomPriceCollection> $price
     */
    public function setPrice(array $price): void
    {
        $this->price = $price;
    }

    public function getCustomerGroupId(): ?string
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(?string $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }

    public function getCustomerGroup(): ?CustomerGroupEntity
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?CustomerGroupEntity $customerGroup): void
    {
        $this->customerGroup = $customerGroup;
    }
}
