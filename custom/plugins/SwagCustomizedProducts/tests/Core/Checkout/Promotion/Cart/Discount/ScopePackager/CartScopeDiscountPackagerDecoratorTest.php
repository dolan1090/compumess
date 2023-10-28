<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Promotion\Cart\Discount\ScopePackager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\Promotion\Cart\Discount\ScopePackager\CartScopeDiscountPackagerDecorator;

class CartScopeDiscountPackagerDecoratorTest extends TestCase
{
    private MockObject&SalesChannelContext $salesChannelContext;

    private MockObject&DiscountPackager $discountPackager;

    private CartScopeDiscountPackagerDecorator $decorator;

    protected function setUp(): void
    {
        $this->salesChannelContext = $this->createMock(SalesChannelContext::class);
        $this->discountPackager = $this->createMock(DiscountPackager::class);

        $this->decorator = new CartScopeDiscountPackagerDecorator($this->discountPackager);
    }

    public function testGetDecorated(): void
    {
        static::assertSame($this->discountPackager, $this->decorator->getDecorated());
    }

    public function testGetMatchingItemsWithoutCustomProductReturnsOriginalItems(): void
    {
        $discountPackageCollection = $this->getDiscountPackageCollection();

        $this->discountPackager->method('getMatchingItems')
            ->willReturn($discountPackageCollection);

        $discountItem = $this->getDiscountLineItem();

        $result = $this->decorator->getMatchingItems($discountItem, new Cart(Uuid::randomHex()), $this->salesChannelContext);
        static::assertSame($discountPackageCollection, $result);
    }

    public function testGetMatchingItems(): void
    {
        $discountPackageCollection = $this->getDiscountPackageCollection();

        $this->discountPackager->method('getMatchingItems')
            ->willReturn($discountPackageCollection);

        $discountItem = $this->getDiscountLineItem();
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $customizedProductLineItemId,
            2
        );

        $customizedProductLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                LineItem::PRODUCT_LINE_ITEM_TYPE
            )
        );

        $cart = new Cart(Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $this->decorator->getMatchingItems($discountItem, $cart, $this->salesChannelContext);
        $customizedProductDiscountPackage = $result->last();
        static::assertNotNull($customizedProductDiscountPackage);
        static::assertCount(2, $customizedProductDiscountPackage->getMetaData());

        $customizedProductLineItemQuantity = $customizedProductDiscountPackage->getMetaData()->first();
        static::assertInstanceOf(LineItemQuantity::class, $customizedProductLineItemQuantity);
        static::assertEquals($customizedProductLineItemId, $customizedProductLineItemQuantity->getLineItemId());
        static::assertEquals(1, $customizedProductLineItemQuantity->getQuantity());
    }

    public function testGetMatchingItemsWithoutProductLineItemGetsRemoved(): void
    {
        $discountPackageCollection = $this->getDiscountPackageCollection();
        $discountItem = $this->getDiscountLineItem();

        $this->discountPackager->method('getMatchingItems')
            ->willReturn($discountPackageCollection);

        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );

        $cart = new Cart(Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $this->decorator->getMatchingItems($discountItem, $cart, $this->salesChannelContext);
        static::assertSame($discountPackageCollection, $result);
    }

    public function testGetMatchingItemsWithMatchingRule(): void
    {
        $discountPackageCollection = $this->getDiscountPackageCollection();

        $this->discountPackager->method('getMatchingItems')
            ->willReturn($discountPackageCollection);

        $rule = (new LineItemRule())
            ->assign(['identifiers' => ['A']]);

        $discountItem = $this->getDiscountLineItem($rule);
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem->addChild(
            new LineItem(
                'A',
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                'A'
            )
        );

        $cart = new Cart(Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $this->decorator->getMatchingItems($discountItem, $cart, $this->salesChannelContext);
        $customizedProductDiscountPackage = $result->last();
        static::assertNotNull($customizedProductDiscountPackage);

        $customizedProductLineItemQuantity = $customizedProductDiscountPackage->getMetaData()->first();
        static::assertNotNull($customizedProductLineItemQuantity);
        static::assertSame($customizedProductLineItemId, $customizedProductLineItemQuantity->getLineItemId());
    }

    public function testGetMatchingItemsWithoutMatchingRuleGetsRemoved(): void
    {
        $discountPackageCollection = $this->getDiscountPackageCollection();

        $this->discountPackager->method('getMatchingItems')
            ->willReturn($discountPackageCollection);

        $rule = (new LineItemRule())
            ->assign(['identifiers' => ['A']]);
        $discountItem = $this->getDiscountLineItem($rule);

        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                'B'
            )
        );

        $cart = new Cart(Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $this->decorator->getMatchingItems($discountItem, $cart, $this->salesChannelContext);
        static::assertSame($discountPackageCollection, $result);
    }

    private function getDiscountLineItem(?Rule $filter = null): DiscountLineItem
    {
        $priceDefinition = new AbsolutePriceDefinition(-10);
        if ($filter !== null) {
            $priceDefinition = new AbsolutePriceDefinition(-10, $filter);
        }

        return new DiscountLineItem('10€ Discount', $priceDefinition, [
            'discountScope' => 'cart',
            'discountType' => 'absolute',
            'filter' => [
                'sorterKey' => 'PRICE_ASC',
                'applierKey' => 'ALL',
                'usageKey' => 'UNLIMITED',
            ],
        ], null);
    }

    private function getDiscountPackageCollection(): DiscountPackageCollection
    {
        return new DiscountPackageCollection([
            new DiscountPackage(new LineItemQuantityCollection()),
        ]);
    }
}
