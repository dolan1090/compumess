<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\LineItem\Group;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\AbstractProductLineItemProvider;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class ProductLineItemProviderDecorator extends AbstractProductLineItemProvider
{
    public function __construct(private readonly AbstractProductLineItemProvider $decorated)
    {
    }

    public function getDecorated(): AbstractProductLineItemProvider
    {
        return $this->decorated;
    }

    public function getProducts(Cart $cart): LineItemCollection
    {
        $collection = $this->decorated->getProducts($cart);

        $customProductsCollection = $cart->getLineItems()->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);

        foreach ($customProductsCollection as $item) {
            $existingItems = $collection->filter(static fn (LineItem $lineItem) => $lineItem->getId() === $item->getId());

            if (\count($existingItems->getElements()) > 0) {
                continue;
            }

            $collection->add($item);
        }

        return $collection;
    }
}
