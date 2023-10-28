<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemCollection;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemEntity;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\PercentageTaxRuleBuilder;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection as PricingCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 *
 * @phpstan-import-type ReturnItemData from OrderReturnLineItemFactory
 * This class is for purpose recalculation refund prices when we change the quantity, add items, or edit the position prices
 */
#[Package('checkout')]
class OrderReturnCalculator
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    /**
     * @internal
     */
    public function __construct(
        private readonly SalesChannelContextRestorer $contextRestorer,
        private readonly EntityRepository $orderReturnRepository,
        private readonly QuantityPriceCalculator $calculator,
        private readonly AmountCalculator $amountCalculator,
        private readonly PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
    }

    public function calculate(string $returnId, Context $context): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        $criteria = new Criteria([$returnId]);
        $criteria->addAssociation('lineItems');

        /** @var OrderReturnEntity $return */
        $return = $this->orderReturnRepository->search($criteria, $context)->first();
        if (!$return->getLineItems()) {
            return;
        }

        $salesChannelContext = $this->contextRestorer->restoreByOrder($return->getOrderId(), $context);

        $updatedPricesLineItems = new OrderReturnLineItemCollection();
        $refundsAmount = new PriceCollection();
        /** @var OrderReturnLineItemEntity $lineItem */
        foreach ($return->getLineItems() as $lineItem) {
            $price = $lineItem->getPrice();
            if (!$price) {
                continue;
            }

            if ($lineItem->getQuantity() !== $price->getQuantity()) {
                $price = $this->calculator->calculate(
                    new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $lineItem->getQuantity()),
                    $salesChannelContext
                );
                $lineItem->setPrice($price);
                $updatedPricesLineItems->add($lineItem);
            }

            // Calculate price for each item
            $refundsAmount->add($this->calculator->calculate(
                new QuantityPriceDefinition($lineItem->getRefundAmount(), $price->getTaxRules()),
                $salesChannelContext
            ));
        }

        $shippingCosts = $this->recalculateShippingCost($return->getShippingCosts(), $refundsAmount, $salesChannelContext);

        // Calculate total refund price
        $returnPrice = $this->amountCalculator->calculate(
            $refundsAmount,
            new PriceCollection([$shippingCosts]),
            $salesChannelContext
        );

        $this->update($returnId, $shippingCosts, $updatedPricesLineItems, $returnPrice, $salesChannelContext);
    }

    private function recalculateShippingCost(?CalculatedPrice $shippingCosts, PriceCollection $refundsAmount, SalesChannelContext $salesChannelContext): CalculatedPrice
    {
        // TODO: so far, the return doesn't have the option to select the Shipping method yet,
        // so we decided to calculate by "Auto" tax calculation type
        $rules = $this->percentageTaxRuleBuilder->buildRules(
            $refundsAmount->sum()
        );

        $shippingCostsPrices = new PricingCollection([
            new Price(
                Defaults::CURRENCY,
                $shippingCosts?->getTotalPrice() ?? 0,
                $shippingCosts?->getTotalPrice() ?? 0,
                false
            ),
        ]);

        $shippingCostsPrice = $this->getCurrencyPrice($shippingCostsPrices, $salesChannelContext);

        $definition = new QuantityPriceDefinition($shippingCostsPrice, $rules, 1);

        return $this->calculator->calculate($definition, $salesChannelContext);
    }

    /**
     * This function is for purpose persist calculated price
     */
    private function update(string $returnId, CalculatedPrice $shippingCosts, OrderReturnLineItemCollection $returnItems, CartPrice $returnPrice, SalesChannelContext $context): void
    {
        $updateReturnData = [
            'id' => $returnId,
            'updatedAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'amountTotal' => $returnPrice->getTotalPrice(),
            'amountNet' => $returnPrice->getNetPrice(),
            'price' => $returnPrice,
            'shippingCosts' => $shippingCosts,
            'orderVersionId' => $context->getContext()->getVersionId(),
        ];

        if ($returnItems->count()) {
            $lineItems = [];
            /** @var OrderReturnLineItemEntity $returnItem */
            foreach ($returnItems as $returnItem) {
                $lineItems[] = [
                    'id' => $returnItem->getId(),
                    'price' => $returnItem->getPrice(),
                ];
            }

            $updateReturnData['lineItems'] = $lineItems;
        }

        $this->orderReturnRepository->upsert([
            $updateReturnData,
        ], $context->getContext());
    }

    private function getCurrencyPrice(PricingCollection $priceCollection, SalesChannelContext $context): float
    {
        /** @var Price $price */
        $price = $priceCollection->getCurrencyPrice($context->getCurrency()->getId());

        $value = $this->getPriceForTaxState($price, $context);

        if ($price->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }
}
