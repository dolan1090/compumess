<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemCollection;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemEntity;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal This class is used for a calculation with Documents that have returned items.
 */
#[Package('checkout')]
class DocumentReturnCalculator
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    /**
     * @internal
     */
    public function __construct(private readonly QuantityPriceCalculator $calculator)
    {
    }

    /**
     * That method is used for calculating a collection of line items again, which has returned items.
     *
     * @internal
     */
    public function calculate(OrderLineItemCollection $lineItems, SalesChannelContext $salesChannelContext): OrderLineItemCollection
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            if ($item->getExtension('returns') === null) {
                continue;
            }

            /** @var OrderReturnLineItemCollection $returnLineItems */
            $returnLineItems = $item->getExtension('returns');
            $returnLineItems = $returnLineItems->getByStates([PositionStateHandler::STATE_RETURNED_PARTIALLY, PositionStateHandler::STATE_RETURNED]);

            if (\count($returnLineItems) === 0) {
                continue;
            }

            $returnQuantity = 0;

            /** @var OrderReturnLineItemEntity $returnLineItem */
            foreach ($returnLineItems as $returnLineItem) {
                $returnQuantity += $returnLineItem->getQuantity();
            }

            if ($returnQuantity === $item->getQuantity()) {
                // Full returned
                $lineItems->remove($item->getId());

                continue;
            }

            /** @var CalculatedPrice */
            $price = $item->getPrice();
            $quantity = $item->getQuantity() - $returnQuantity;
            $adjustPrice = $this->calculator->calculate(new QuantityPriceDefinition($item->getUnitPrice(), $price->getTaxRules(), $quantity), $salesChannelContext);

            $item->setPrice($adjustPrice);
            $item->setTotalPrice($adjustPrice->getTotalPrice());
            $item->setQuantity($quantity);
        }

        return $lineItems;
    }
}
