<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Promotion\Cart\Discount\Filter;

use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\Filter\SetGroupScopeFilter;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class AdvancedPackageRulesDecorator extends SetGroupScopeFilter
{
    public function __construct(private readonly SetGroupScopeFilter $decorated)
    {
    }

    public function getDecorated(): SetGroupScopeFilter
    {
        return $this->decorated;
    }

    public function filter(DiscountLineItem $discount, DiscountPackageCollection $packages, SalesChannelContext $context): DiscountPackageCollection
    {
        $priceDefinition = $discount->getPriceDefinition();
        $customProductIds = [];

        $customProductsPackages = $packages->filter(static function (DiscountPackage $package) use (&$customProductIds) {
            $lineItems = $package->getCartItems()->filter(static fn (LineItem $lineItem) => $lineItem->getType() === CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);

            $lineItemIds = $lineItems->map(static fn (LineItem $lineItem) => $lineItem->getId());

            $customProductIds = [...$customProductIds, ...$lineItemIds];

            return \count($lineItems) > 0;
        });

        // remove custom product line items from decorated packages
        $originalPackages = $this->decorated->filter($discount, $packages, $context)
            ->filter(static function (DiscountPackage $package) use ($customProductIds) {
                $lineItems = $package->getMetaData()->filter(static fn (LineItemQuantity $itemQuantity) => !\in_array($itemQuantity->getLineItemId(), $customProductIds, true));

                return \count($lineItems) > 0;
            });

        foreach ($customProductsPackages as $package) {
            $foundItems = [];

            foreach ($package->getMetaData() as $item) {
                $lineItem = $package->getCartItem($item->getLineItemId());

                if ($this->isRulesFilterValid($lineItem, $priceDefinition, $context)) {
                    $item = new LineItemQuantity(
                        $lineItem->getId(),
                        $lineItem->getQuantity()
                    );

                    $foundItems[] = $item;
                }
            }

            if (\count($foundItems) > 0) {
                $originalPackages->add(new DiscountPackage(new LineItemQuantityCollection($foundItems)));
            }
        }

        return $originalPackages;
    }

    private function isRulesFilterValid(LineItem $item, PriceDefinitionInterface $priceDefinition, SalesChannelContext $context): bool
    {
        // if the price definition doesnt allow filters,
        // then return valid for the item
        if (!\method_exists($priceDefinition, 'getFilter')) {
            return true;
        }

        /** @var Rule|null $filter */
        $filter = $priceDefinition->getFilter();

        // if the definition exists, but is empty
        // this means we have no restrictions and thus its valid
        if (!$filter instanceof Rule) {
            return true;
        }

        $productLineItem = $item->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
        if ($productLineItem === null) {
            return false;
        }

        // if our price definition has a filter rule
        // then extract it, and check if it matches
        $scope = new LineItemScope($productLineItem, $context);

        return $filter->match($scope);
    }
}
