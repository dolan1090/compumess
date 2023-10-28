<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Components\CustomerPrice;

use Acris\CustomerPrice\Components\PriceRoundingService;
use Acris\CustomerPrice\Components\Struct\LineItemCustomerPriceStruct;
use Acris\CustomerPrice\Custom\CustomerAdvancedPriceEntity;
use Acris\CustomerPrice\Custom\CustomerPriceCollection;
use Acris\CustomerPrice\Custom\CustomerPriceEntity;
use Acris\CustomerPrice\Core\Checkout\Cart\Price\Struct\AcrisListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\RegulationPrice;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class CustomerPriceService
{
    const DEFAULT_LIST_PRICE_TYPE_IF_EMPTY = 'ifEmptyUseOriginal';
    const DEFAULT_LIST_PRICE_TYPE_IF_BOTH_EMPTY = 'ifBothEmptyUseNormalPrice';
    const ACRIS_CUSTOMER_PRICE_LINE_ITEM_DISCOUNT = 'acrisCustomerPriceLineItemDiscount';

    public function __construct(private readonly CustomerPriceGateway $customerPriceGateway, private readonly PriceRoundingService $priceRoundingService, private readonly SystemConfigService  $systemConfigService, private ?CustomerPriceCollection $customerPriceCollection = null, private array $ruleIds = [])
    {
    }

    public function getCustomerPricesForProductIds(SalesChannelContext $salesChannelContext, array $ids): CustomerPriceCollection
    {
        if(!$salesChannelContext->getCustomer() instanceof CustomerEntity) {
            return new CustomerPriceCollection();
        }

        $ruleCompare = count(array_intersect($salesChannelContext->getRuleIds(), $this->ruleIds)) === count($salesChannelContext->getRuleIds());

        if (empty($this->customerPriceCollection) || !$this->customerPriceCollection instanceof CustomerPriceCollection || !$ruleCompare) {
            $this->customerPriceCollection = new CustomerPriceCollection();
        }

        $ids = $this->filterProductIds($salesChannelContext->getCustomer()->getId(), $ids, $this->customerPriceCollection);

        if (empty($ids)) {
            $this->ruleIds = $salesChannelContext->getRuleIds();
            return $this->customerPriceCollection;
        }

        $this->customerPriceCollection->merge($this->customerPriceGateway->loadCustomerPricesWithProductIds($salesChannelContext, $ids, $salesChannelContext->getCustomer()->getId()));

        $this->ruleIds = $salesChannelContext->getRuleIds();

        return $this->customerPriceCollection;
    }

    public function calculateProductPrices(iterable $products, SalesChannelContext $salesChannelContext): void
    {
        $productIds = $this->getProductIds($products);

        if(empty($productIds)) {
            return;
        }

        $customerPrices = $this->getCustomerPricesForProductIds($salesChannelContext, $productIds);

        if ($customerPrices->count() <= 0) return;

        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {
            /** @var CustomerPriceEntity $customerPrice */
            $customerPrice = $customerPrices->filter(function (CustomerPriceEntity $customerPrice) use ($product) {
                if ($product->getId() === $customerPrice->getProductId()) {
                    return true;
                }

                return false;
            })->first();

            if (!$customerPrice) continue;

            $prices = $customerPrice->getAcrisPrices();
            if ($prices->count() > 0) {
                $prices->sortByQuantity();
                $priceCollection = new PriceCollection();
                $calculatedPrices = new CalculatedPriceCollection();
                $productPriceCollection = new ProductPriceCollection();
                $calculatedPriceFirst = null;

                foreach ($prices as $price) {
                    foreach ($price->getPrice() as $value) {
                        $priceCollection->add($value);
                    }
                    $quantity = $price->getQuantityEnd() ? $price->getQuantityEnd() : $price->getQuantityStart();

                    $productTaxPrice = $this->getPriceForTaxState($price->getPrice()->first(), $salesChannelContext);
                    $listPrice = $this->getListPriceForCustomerPrice($productTaxPrice, $price, $customerPrice->getListPriceType(), $product, $salesChannelContext);
                    if (!empty($listPrice) && !$listPrice instanceof ListPrice) {
                        $listPriceTaxPrice = $this->getPriceForTaxState($listPrice, $salesChannelContext);
                        if ($productTaxPrice === 0.0 && $listPriceTaxPrice === 0.0) {
                            $listPrice = null;
                        } else {
                            $listPrice = ListPrice::createFromUnitPrice($productTaxPrice, $listPriceTaxPrice);
                        }
                    }

                    $regulationPrice = $this->getRegulationPriceForCustomerPrice($price, $customerPrice->getListPriceType(), $product);

                    if (!empty($regulationPrice) && !$regulationPrice instanceof RegulationPrice) {
                        $regulationPriceTaxPrice = $this->getPriceForTaxState($regulationPrice, $salesChannelContext);
                        $regulationPrice = new RegulationPrice($regulationPriceTaxPrice);
                    }

                    $calculatedPrice = new CalculatedPrice($productTaxPrice, $quantity * $productTaxPrice, $product->getCalculatedPrice()->getCalculatedTaxes(), $product->getCalculatedPrice()->getTaxRules(), $quantity, null, $listPrice, $regulationPrice);
                    $calculatedPrice = $this->roundCalculatedPrice( $calculatedPrice, $salesChannelContext->getSalesChannelId() );
                    $calculatedPrices->add($calculatedPrice);

                    if ($product instanceof SalesChannelProductEntity) {
                        $this->persistExtensions($product, $priceCollection);
                    }

                    $this->setProductPrice($productPriceCollection, $product, $salesChannelContext, $price);

                    if($price->getQuantityStart() === 1) {
                        $calculatedPriceFirst = new CalculatedPrice($productTaxPrice, $productTaxPrice, $product->getCalculatedPrice()->getCalculatedTaxes(), $product->getCalculatedPrice()->getTaxRules(), 1, null, $listPrice, $regulationPrice);
                    }

                    $calculatedCheapestPrice = new CalculatedCheapestPrice($productTaxPrice, $quantity * $productTaxPrice, $product->getCalculatedPrice()->getCalculatedTaxes(), $product->getCalculatedPrice()->getTaxRules(), $quantity, null, $listPrice);
                    $product->setCalculatedCheapestPrice($calculatedCheapestPrice);
                    $product->getCheapestPrice()->setPrice($price->getPrice());
                }

                $this->addLineItemDiscountData($product);
                $product->setPrice($priceCollection);
                $product->setCalculatedPrices($calculatedPrices);
                if($calculatedPriceFirst instanceof CalculatedPrice) {
                    $product->setCalculatedPrice($calculatedPriceFirst);
                }
                $product->setPrices($productPriceCollection);
            }
        }
    }

    private function persistExtensions(SalesChannelProductEntity $product, $price) {
        $extensions = $product->getCalculatedPrice()->getExtensions();
        $priceExtensions = $price->getExtensions();
        foreach ($extensions as $key => $extension) {
            $priceExtensions[$key] = $extension;
        }
        $price->setExtensions($priceExtensions);
    }

    private function getListPriceForCustomerPrice(float $newUnitPrice, CustomerAdvancedPriceEntity $price, ?string $listPriceType, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext)
    {
        $listPrice = $price->getPrice() && $price->getPrice()->first() && $price->getPrice()->first()->getListPrice() ? $price->getPrice()->first()->getListPrice(): null;

        switch ($listPriceType) {
            case self::DEFAULT_LIST_PRICE_TYPE_IF_EMPTY:
                if (!$listPrice && $product->getCalculatedPrice()->getListPrice()) {
                    $listPrice = ListPrice::createFromUnitPrice($newUnitPrice, $product->getCalculatedPrice()->getListPrice()->getPrice());
                }
                return $listPrice;

            case self::DEFAULT_LIST_PRICE_TYPE_IF_BOTH_EMPTY:
                if(!$listPrice) {
                    if(!empty($product->getCalculatedPrice()->getListPrice())) {
                        $listPrice = ListPrice::createFromUnitPrice($newUnitPrice, $product->getCalculatedPrice()->getListPrice()->getPrice());
                    } else {
                        if($product->getPrice() && $product->getPrice()->first()) {
                            $listPrice = ListPrice::createFromUnitPrice($newUnitPrice, $this->getPriceForTaxState($product->getPrice()->first(), $salesChannelContext));
                        }
                    }
                }
                return $listPrice;
            default:
                return $listPrice;
        }
    }

    private function getRegulationPriceForCustomerPrice(CustomerAdvancedPriceEntity $price, ?string $listPriceType, SalesChannelProductEntity $product)
    {
        return $price->getPrice() && $price->getPrice()->first() && $price->getPrice()->first()->getRegulationPrice() ? $price->getPrice()->first()->getRegulationPrice() : null;
    }

    private function setProductPrice(ProductPriceCollection $productPriceCollection, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext, CustomerAdvancedPriceEntity $price): void
    {
        $productPrice = new ProductPriceEntity();
        $productPrice->setProductId($product->getId());
        $productPrice->setQuantityStart($price->getQuantityStart());
        $productPrice->setQuantityEnd($price->getQuantityEnd());
        $productPrice->setPrice($price->getPrice());
        $productPrice->setUniqueIdentifier($price->getUniqueIdentifier());
        $productPrice->setId($price->getId());
        $productPrice->setRuleId($salesChannelContext->getRuleIds()[0]);
        $productPrice->setVersionId($price->getVersionId());
        $productPrice->setCreatedAt($price->getCreatedAt());
        if (!empty($price->getUpdatedAt())) {
            $productPrice->setUpdatedAt($price->getUpdatedAt());
        }
        $productPrice->setExtensions($price->getExtensions());
        $productPriceCollection->add($productPrice);
    }

    private function getProductIds(iterable $products): array
    {
        $productIds = [];
        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }
        return array_unique($productIds);
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }

    private function addLineItemDiscountData(SalesChannelProductEntity $product): void
    {
        /** @var LineItemCustomerPriceStruct $lineItemDiscount */
        $lineItemDiscount = new LineItemCustomerPriceStruct( $product->getCalculatedPrice()->getUnitPrice());

        if (!$product->hasExtension(self::ACRIS_CUSTOMER_PRICE_LINE_ITEM_DISCOUNT))
        $product->addExtension( self::ACRIS_CUSTOMER_PRICE_LINE_ITEM_DISCOUNT, $lineItemDiscount );
    }

    private function roundCalculatedPrice( CalculatedPrice $calculatedPrice, string $salesChannelId ): CalculatedPrice
    {
        if( $listPrice = $calculatedPrice->getListPrice() )
        {
            $roundingType =  $this->systemConfigService->getString('AcrisCustomerPriceCS.config.typeOfRounding', $salesChannelId );
            $decimalPlaces =  intval( $this->systemConfigService->get('AcrisCustomerPriceCS.config.decimalPlaces', $salesChannelId ) );

            $percentageRounded = $this->priceRoundingService->round( $listPrice->getPercentage(), $decimalPlaces, $roundingType );
            $listPriceNew = new AcrisListPrice( $listPrice->getPrice(), $listPrice->getDiscount(), $percentageRounded );
            $calculatedPrice =  new CalculatedPrice(
                $calculatedPrice->getUnitPrice(),
                $calculatedPrice->getTotalPrice(),
                $calculatedPrice->getCalculatedTaxes(),
                $calculatedPrice->getTaxRules(),
                $calculatedPrice->getQuantity(),
                $calculatedPrice->getReferencePrice(),
                $listPriceNew,
                $calculatedPrice->getRegulationPrice()
            );
        }
        return $calculatedPrice;
    }

    private function filterProductIds(string $customerId, array $ids, ?CustomerPriceCollection $customerPriceCollection): array
    {
        if (empty($customerPriceCollection) || $customerPriceCollection->count() === 0) {
            return $ids;
        }

        foreach ($ids as $key => $id) {
            $customerPrice = $customerPriceCollection->filter(function (CustomerPriceEntity $customerPrice) use ($id, $customerId) {
                return $id === $customerPrice->getProductId() && $customerId === $customerPrice->getCustomerId();
            })->first();

            if (!empty($customerPrice)) {
                unset($ids[$key]);
            }
        }

        return $ids;
    }
}
