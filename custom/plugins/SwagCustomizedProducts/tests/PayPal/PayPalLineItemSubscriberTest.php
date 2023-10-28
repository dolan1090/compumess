<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\PayPal;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\TestDefaults;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\PayPal\PayPalLineItemSubscriber;
use Swag\PayPal\OrdersApi\Builder\Event\PayPalV2ItemFromCartEvent;
use Swag\PayPal\OrdersApi\Builder\Event\PayPalV2ItemFromOrderEvent;
use Swag\PayPal\PaymentsApi\Builder\Event\PayPalV1ItemFromCartEvent;
use Swag\PayPal\PaymentsApi\Builder\Event\PayPalV1ItemFromOrderEvent;
use Swag\PayPal\RestApi\V2\Api\Order\PurchaseUnit\Item;
use Swag\PayPal\SwagPayPal;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PayPalLineItemSubscriberTest extends TestCase
{
    use KernelTestBehaviour;

    private const INITIAL_PAYPAL_ITEM_NAME = 'PayPal item';
    private const INITIAL_PAYPAL_ITEM_SKU = 'SW-INITIAL';
    private const PRODUCT_NAME = 'Product Test Name';
    private const PRODUCT_NUMBER = 'SW-10000';
    private const OPTION_NAME = 'Custom Products Test Option Name';
    private const OPTION_VALUE_NAME = 'Custom Products Test Option Value Name';
    private const OPTION_VALUE_NUMBER = 'SW-CP-OV-1';

    /**
     * @before
     */
    public function isPayPalAvailable(): void
    {
        $paypalBaseClass = $this->getContainer()->get(SwagPayPal::class, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($paypalBaseClass === null) {
            static::markTestSkipped('Skipping because SwagPayPal is not installed');
        }
    }

    public function testGetSubscribedEvents(): void
    {
        $events = PayPalLineItemSubscriber::getSubscribedEvents();

        static::assertCount(4, $events);
        static::assertSame('adjustPayPalItemFromOrder', $events[PayPalV1ItemFromOrderEvent::class]);
        static::assertSame('adjustPayPalItemFromCart', $events[PayPalV1ItemFromCartEvent::class]);
        static::assertSame('adjustPayPalItemFromOrder', $events[PayPalV2ItemFromOrderEvent::class]);
        static::assertSame('adjustPayPalItemFromCart', $events[PayPalV2ItemFromCartEvent::class]);
    }

    public function testAdjustPayPalV2ItemFromOrderIsNotCustomProduct(): void
    {
        $event = $this->createOrderEvent(LineItem::PRODUCT_LINE_ITEM_TYPE, false);
        $this->createSubscriber()->adjustPayPalItemFromOrder($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertSame(self::INITIAL_PAYPAL_ITEM_NAME, $paypalItem->getName());
        static::assertSame(self::INITIAL_PAYPAL_ITEM_SKU, $paypalItem->getSku());
    }

    public function testAdjustPayPalV2ItemFromOrderHasNoChildren(): void
    {
        $event = $this->createOrderEvent(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            false
        );
        $this->createSubscriber()->adjustPayPalItemFromOrder($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertSame(self::INITIAL_PAYPAL_ITEM_NAME, $paypalItem->getName());
        static::assertSame(self::INITIAL_PAYPAL_ITEM_SKU, $paypalItem->getSku());
    }

    public function testAdjustPayPalV2ItemFromOrder(): void
    {
        $event = $this->createOrderEvent();
        $this->createSubscriber()->adjustPayPalItemFromOrder($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertNotSame(self::INITIAL_PAYPAL_ITEM_NAME, $paypalItem->getName());
        static::assertNotSame(self::INITIAL_PAYPAL_ITEM_SKU, $paypalItem->getSku());
        static::assertSame(
            \sprintf(
                '%s (incl. configuration: %s (%s))',
                self::PRODUCT_NAME,
                self::OPTION_NAME,
                self::OPTION_VALUE_NAME
            ),
            $paypalItem->getName()
        );
        static::assertSame(
            \sprintf('%s (incl. configuration: %s)', self::PRODUCT_NUMBER, self::OPTION_VALUE_NUMBER),
            $paypalItem->getSku()
        );
    }

    public function testAdjustPayPalV2ItemFromCartIsNotCustomProduct(): void
    {
        $event = $this->createCartEvent(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $this->createSubscriber()->adjustPayPalItemFromCart($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertSame(self::INITIAL_PAYPAL_ITEM_NAME, $paypalItem->getName());
        static::assertSame(self::INITIAL_PAYPAL_ITEM_SKU, $paypalItem->getSku());
    }

    public function testAdjustPayPalV2ItemFromCartWithoutProductNummer(): void
    {
        $event = $this->createCartEvent();
        $this->createSubscriber()->adjustPayPalItemFromCart($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertNotSame(self::INITIAL_PAYPAL_ITEM_NAME, $paypalItem->getName());
        static::assertSame(self::PRODUCT_NAME, $paypalItem->getName());
        static::assertNull($paypalItem->getSku());
    }

    public function testAdjustPayPalV2ItemFromCartNameAndNumberTooLong(): void
    {
        $productName = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam volu';
        $productNumber = 'SW-100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

        $event = $this->createCartEvent();
        $productCartItem = $event->getOriginalShopwareLineItem()->getChildren()->first();
        static::assertNotNull($productCartItem);
        $productCartItem->setLabel($productName);
        $productCartItem->setPayloadValue('productNumber', $productNumber);
        $this->createSubscriber()->adjustPayPalItemFromCart($event);

        $paypalItem = $event->getPayPalLineItem();
        static::assertSame($paypalItem::MAX_LENGTH_NAME, \strlen((string) $paypalItem->getName()));
        static::assertSame($paypalItem::MAX_LENGTH_SKU, \strlen((string) $paypalItem->getSku()));
    }

    private function createSubscriber(): PayPalLineItemSubscriber
    {
        /** @var Translator $translator */
        $translator = $this->getContainer()->get('translator');
        $translator->injectSettings(
            TestDefaults::SALES_CHANNEL,
            Defaults::LANGUAGE_SYSTEM,
            'en-GB',
            Context::createDefaultContext()
        );

        return new PayPalLineItemSubscriber(
            new NullLogger(),
            $translator
        );
    }

    private function createOrderEvent(
        string $orderLineItemType = CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
        bool $withChildren = true
    ): PayPalV2ItemFromOrderEvent {
        $parentLineItemId = Uuid::randomHex();
        $orderLineItem = new OrderLineItemEntity();
        $orderLineItem->setId($parentLineItemId);
        $orderLineItem->setType($orderLineItemType);

        if ($withChildren) {
            $children = $this->createOrderChildLineItems($parentLineItemId);
            $orderLineItem->setChildren($children);
        }

        return new PayPalV2ItemFromOrderEvent($this->createPayPalItem(), $orderLineItem);
    }

    private function createPayPalItem(): Item
    {
        $item = new Item();
        $item->setName(self::INITIAL_PAYPAL_ITEM_NAME);
        $item->setSku(self::INITIAL_PAYPAL_ITEM_SKU);

        return $item;
    }

    private function createOrderChildLineItems(string $parentLineItemId): OrderLineItemCollection
    {
        $productLineItem = $this->createOrderProductLineItem($parentLineItemId);
        $cpOptionLineItem = $this->createOrderOptionLineItem($parentLineItemId);

        return new OrderLineItemCollection([$productLineItem, $cpOptionLineItem]);
    }

    private function createOrderProductLineItem(string $parentLineItemId): OrderLineItemEntity
    {
        $productLineItem = new OrderLineItemEntity();
        $productLineItem->setId(Uuid::randomHex());
        $productLineItem->setParentId($parentLineItemId);
        $productLineItem->setLabel(self::PRODUCT_NAME);
        $productLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $productLineItem->setPayload(['productNumber' => self::PRODUCT_NUMBER]);

        return $productLineItem;
    }

    private function createOrderOptionLineItem(string $parentLineItemId): OrderLineItemEntity
    {
        $cpOptionLineItemId = Uuid::randomHex();
        $cpOptionLineItem = new OrderLineItemEntity();
        $cpOptionLineItem->setId($cpOptionLineItemId);
        $cpOptionLineItem->setParentId($parentLineItemId);
        $cpOptionLineItem->setLabel(self::OPTION_NAME);
        $cpOptionLineItem->setType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        $cpOptionLineItem->setChildren($this->createOrderOptionChildLineItems($cpOptionLineItemId));

        return $cpOptionLineItem;
    }

    private function createOrderOptionChildLineItems(string $parentLineItemId): OrderLineItemCollection
    {
        $cpOptionValueLineItem = new OrderLineItemEntity();
        $cpOptionValueLineItem->setId(Uuid::randomHex());
        $cpOptionValueLineItem->setParentId($parentLineItemId);
        $cpOptionValueLineItem->setLabel(self::OPTION_VALUE_NAME);
        $cpOptionValueLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $cpOptionValueLineItem->setPayload(['productNumber' => self::OPTION_VALUE_NUMBER]);

        return new OrderLineItemCollection([$cpOptionValueLineItem]);
    }

    private function createCartEvent(
        string $cartLineItemType = CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
    ): PayPalV2ItemFromCartEvent {
        $cartLineItem = new LineItem(Uuid::randomHex(), $cartLineItemType);
        $cartLineItem->addChild($this->createCartProductLineItem());
        $cartLineItem->addChild($this->createCartOptionLineItem());

        return new PayPalV2ItemFromCartEvent($this->createPayPalItem(), $cartLineItem);
    }

    private function createCartProductLineItem(): LineItem
    {
        $product = new LineItem(Uuid::randomHex(), LineItem::PRODUCT_LINE_ITEM_TYPE);
        $product->setLabel(self::PRODUCT_NAME);

        return $product;
    }

    private function createCartOptionLineItem(): LineItem
    {
        $option = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $optionValue = new LineItem(Uuid::randomHex(), LineItem::CREDIT_LINE_ITEM_TYPE);
        $option->addChild($optionValue);

        return $option;
    }
}
