<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Struct\Struct;

class CustomerPriceEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    /**
     * @var boolean|null
     */
    protected $active;

    /**
     * @var \DateTimeInterface|null
     */
    protected $activeFrom;

    /**
     * @var \DateTimeInterface|null
     */
    protected $activeUntil;

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var string
     */
    protected $productVersionId;

    /**
     * @var ProductEntity|null
     */
    protected $product;

    /**
     * @var array|null
     */
    protected $ruleIds;

    /**
     * @var string
     */
    protected $customerId;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var string|null
     */
    protected $listPriceType;

    /**
     * @var CustomerAdvancedPriceCollection|null
     */
    protected $acrisPrices;

    /**
     * @var RuleCollection|null
     */
    protected $rules;

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getActiveFrom(): ?\DateTimeInterface
    {
        return $this->activeFrom;
    }

    /**
     * @param \DateTimeInterface|null $activeFrom
     */
    public function setActiveFrom(?\DateTimeInterface $activeFrom): void
    {
        $this->activeFrom = $activeFrom;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getActiveUntil(): ?\DateTimeInterface
    {
        return $this->activeUntil;
    }

    /**
     * @param \DateTimeInterface|null $activeUntil
     */
    public function setActiveUntil(?\DateTimeInterface $activeUntil): void
    {
        $this->activeUntil = $activeUntil;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return ProductEntity|null
     */
    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity|null $product
     */
    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity|null $customer
     */
    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string|null
     */
    public function getListPriceType(): ?string
    {
        return $this->listPriceType;
    }

    /**
     * @param string|null $listPriceType
     */
    public function setListPriceType(?string $listPriceType): void
    {
        $this->listPriceType = $listPriceType;
    }

    /**
     * @return CustomerAdvancedPriceCollection|null
     */
    public function getAcrisPrices(): ?CustomerAdvancedPriceCollection
    {
        return $this->acrisPrices;
    }

    /**
     * @param CustomerAdvancedPriceCollection|null $acrisPrices
     */
    public function setAcrisPrices(?CustomerAdvancedPriceCollection $acrisPrices): void
    {
        $this->acrisPrices = $acrisPrices;
    }

    /**
     * @return RuleCollection|null
     */
    public function getRules(): ?RuleCollection
    {
        return $this->rules;
    }

    /**
     * @param RuleCollection|null $rules
     */
    public function setRules(?RuleCollection $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * @return Struct[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param Struct[] $extensions
     */
    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }

    /**
     * @return array|null
     */
    public function getRuleIds(): ?array
    {
        return $this->ruleIds;
    }

    /**
     * @param array|null $ruleIds
     */
    public function setRuleIds(?array $ruleIds): void
    {
        $this->ruleIds = $ruleIds;
    }

    /**
     * @return string
     */
    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    /**
     * @param string $productVersionId
     */
    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }
}
