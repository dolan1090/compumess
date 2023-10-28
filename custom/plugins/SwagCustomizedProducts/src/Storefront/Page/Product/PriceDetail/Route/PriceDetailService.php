<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class PriceDetailService
{
    public function getProductPrice(LineItem $customizedProductLineItem): ?CalculatedPrice
    {
        $productLineItem = $customizedProductLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
        if ($productLineItem === null) {
            return null;
        }

        return $productLineItem->getPrice();
    }

    public function getSurcharges(LineItem $customizedProductLineItem): array
    {
        $surcharges = [];
        $oneTimeSurcharges = [];
        $optionLineItems = $customizedProductLineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );

        $this->splitSurcharges($surcharges, $oneTimeSurcharges, $optionLineItems);

        return [
            $surcharges,
            $oneTimeSurcharges,
        ];
    }

    private function splitSurcharges(array &$surcharges, array &$oneTimeSurcharges, LineItemCollection $lineItems, ?LineItem $parent = null): void
    {
        foreach ($lineItems as $lineItem) {
            $price = $lineItem->getPrice();
            $label = $lineItem->getLabel();
            if ($price === null || $label === null || $price->getTotalPrice() <= 0.0) {
                if ($lineItem->hasChildren()) {
                    $this->splitSurcharges($surcharges, $oneTimeSurcharges, $lineItem->getChildren(), $lineItem);
                }

                continue;
            }

            $parentLabel = '';
            if ($parent !== null && $parent->getLabel() !== null) {
                $parentLabel = $parent->getLabel();
            }

            if ($lineItem->getPayloadValue('isOneTimeSurcharge')) {
                $oneTimeSurcharges[$lineItem->getId()] = [
                    'parentLabel' => $parentLabel,
                    'label' => $label,
                    'price' => $price->getTotalPrice(),
                ];
            } else {
                $surcharges[$lineItem->getId()] = [
                    'parentLabel' => $parentLabel,
                    'label' => $label,
                    'price' => $price->getTotalPrice(),
                ];
            }

            if ($lineItem->hasChildren()) {
                $this->splitSurcharges($surcharges, $oneTimeSurcharges, $lineItem->getChildren(), $lineItem);
            }
        }
    }
}
