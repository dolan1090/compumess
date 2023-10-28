<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceRuleEntity;
use Shopware\Core\Framework\Struct\Struct;

class CustomerAdvancedPriceEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    /**
     * @var string
     */
    protected $customerPriceId;

    /**
     * @var PriceCollection
     */
    protected $price;

    /**
     * @var int
     */
    protected $quantityStart;

    /**
     * @var int|null
     */
    protected $quantityEnd;

    /**
     * @var CustomerPriceEntity|null
     */
    protected $customerPrice;

    /**
     * @return string
     */
    public function getCustomerPriceId(): string
    {
        return $this->customerPriceId;
    }

    /**
     * @param string $customerPriceId
     */
    public function setCustomerPriceId(string $customerPriceId): void
    {
        $this->customerPriceId = $customerPriceId;
    }

    /**
     * @return PriceCollection
     */
    public function getPrice(): PriceCollection
    {
        return $this->price;
    }

    /**
     * @param PriceCollection $price
     */
    public function setPrice(PriceCollection $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getQuantityStart(): int
    {
        return $this->quantityStart;
    }

    /**
     * @param int $quantityStart
     */
    public function setQuantityStart(int $quantityStart): void
    {
        $this->quantityStart = $quantityStart;
    }

    /**
     * @return int|null
     */
    public function getQuantityEnd(): ?int
    {
        return $this->quantityEnd;
    }

    /**
     * @param int|null $quantityEnd
     */
    public function setQuantityEnd(?int $quantityEnd): void
    {
        $this->quantityEnd = $quantityEnd;
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
     * @return CustomerPriceEntity|null
     */
    public function getCustomerPrice(): ?CustomerPriceEntity
    {
        return $this->customerPrice;
    }

    /**
     * @param CustomerPriceEntity|null $customerPrice
     */
    public function setCustomerPrice(?CustomerPriceEntity $customerPrice): void
    {
        $this->customerPrice = $customerPrice;
    }
}
