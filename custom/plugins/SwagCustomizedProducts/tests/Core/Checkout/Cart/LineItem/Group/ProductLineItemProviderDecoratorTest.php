<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Cart\LineItem\Group;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\AbstractProductLineItemProvider;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Swag\CustomizedProducts\Core\Checkout\Cart\LineItem\Group\ProductLineItemProviderDecorator;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class ProductLineItemProviderDecoratorTest extends TestCase
{
    public function testGetDecorated(): void
    {
        /**
         * @var AbstractProductLineItemProvider|MockObject $providerMock
         */
        $providerMock = $this->createMock(AbstractProductLineItemProvider::class);
        $providerDecorator = new ProductLineItemProviderDecorator($providerMock);

        static::assertSame($providerMock, $providerDecorator->getDecorated());
    }

    public function testGetProducts(): void
    {
        /**
         * @var AbstractProductLineItemProvider|MockObject $providerMock
         */
        $providerMock = $this->createMock(AbstractProductLineItemProvider::class);
        $providerMock->method('getProducts')
            ->willReturn(new LineItemCollection([
                new LineItem('product', LineItem::PRODUCT_LINE_ITEM_TYPE),
            ]));

        $providerDecorator = new ProductLineItemProviderDecorator($providerMock);

        $cart = new Cart('test');
        $cart->addLineItems(new LineItemCollection([
            new LineItem('product', LineItem::PRODUCT_LINE_ITEM_TYPE),
            new LineItem('custom', CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE),
        ]));

        static::assertCount(2, $providerDecorator->getProducts($cart));
    }
}
