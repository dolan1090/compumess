<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\SalesChannel\CartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Content\Test\Product\ProductBuilder;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestDataCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\TestDefaults;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartProcessor;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Exception\NoProductException;
use Symfony\Component\HttpFoundation\Request;

class CustomizedProductsCartProcessorTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const CART_TOKEN = 'test-töken';
    private const CART_TO_CALCULATE_TOKEN = 'test-to-calculate';
    private const SALES_CHANNEL_TOKEN = 'sales-channel-token';

    private CustomizedProductsCartProcessor $processor;

    private CartService $cartService;

    private CachedSalesChannelContextFactory $salesChannelContextFactory;

    private TestDataCollection $ids;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->processor = $container->get(CustomizedProductsCartProcessor::class);
        $this->cartService = $container->get(CartService::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->ids = new TestDataCollection();
    }

    public function testThatProcessExitsIfLineItemHasNoReferencedId(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateLineItemId = Uuid::randomHex();

        $customizedProductsLineItem = new LineItem(
            $templateLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductsLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            Uuid::randomHex()
        );

        $cart->add($customizedProductsLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $errorCollection = $toCalculate->getErrors();
        static::assertCount(0, $toCalculate->getLineItems());
        static::assertCount(1, $errorCollection);
        $error = $errorCollection->first();
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $error);
        static::assertSame([
            'id' => $templateLineItemId,
        ], $error->getParameters());
        static::assertFalse($error->blockOrder());
        static::assertSame(Error::LEVEL_ERROR, $error->getLevel());
    }

    public function testThatProcessRemovesLineItemsWithoutConfigurationHash(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );

        $customizedProductsLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );

        $cart->add($customizedProductsLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $errorCollection = $toCalculate->getErrors();
        static::assertCount(0, $toCalculate->getLineItems());
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testThatProcessGroupsSameConfigurationHashes(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );

        $productLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $productLineItem->setPriceDefinition(
            new QuantityPriceDefinition(
                100.0,
                new TaxRuleCollection()
            )
        );

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $firstLineItem->addChild($productLineItem);
        $cart->add($firstLineItem);

        $secondLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $secondLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $secondLineItem->addChild($productLineItem);
        $cart->add($secondLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        static::assertSame(2, $lineItem->getQuantity());
    }

    public function testThatProcessWithoutProductThrowsException(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $cart->add($firstLineItem);

        $this->expectException(NoProductException::class);
        $this->expectExceptionMessage('The template with the ID ' . $templateId . ' has no product');

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessProductWithoutPriceDefinitionThrowsException(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $firstLineItem->addChild($productLineItem);
        $cart->add($firstLineItem);

        $this->expectException(CartException::class);
        $this->expectExceptionMessage(
            'Line item with identifier ' . $productLineItemId . ' has no price.'
        );

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessCalculateOptionPriceWithoutPriceDefinition(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $productLineItem->setPriceDefinition(
            new QuantityPriceDefinition(
                100.0,
                new TaxRuleCollection()
            )
        );
        $customProductsTemplateLineItem->addChild($productLineItem);

        $customizedProductsOptionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        $optionLineItems = $lineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        static::assertCount(1, $optionLineItems);
        static::assertNull($optionLineItems->getPrices()->first());
    }

    public function testProcessThrowsExceptionWithUnsupportedPriceDefinition(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $priceDefinition = new QuantityPriceDefinition(100.0, new TaxRuleCollection());
        $productLineItem->setPriceDefinition($priceDefinition);
        $customProductsTemplateLineItem->addChild($productLineItem);

        $optionLineItemId = Uuid::randomHex();
        $customizedProductsOptionLineItem = new LineItem(
            $optionLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductsOptionLineItem->setPriceDefinition(new DummyPriceDefinition());
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->expectException(CartException::class);
        $this->expectExceptionMessage(
            'Line item with identifier ' . $optionLineItemId . ' has no price.'
        );

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessCalculateOptionPriceWithChildren(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $priceDefinition = new QuantityPriceDefinition(100.0, new TaxRuleCollection());
        $productLineItem->setPriceDefinition($priceDefinition);
        $customProductsTemplateLineItem->addChild($productLineItem);

        $customizedProductsOptionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductsOptionLineItem->setPriceDefinition($priceDefinition);
        $customizedProductsOptionValueLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $customizedProductsOptionValueLineItem->setPriceDefinition($priceDefinition);
        $customizedProductsOptionLineItem->addChild($customizedProductsOptionValueLineItem);
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        $optionLineItems = $lineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        static::assertCount(1, $optionLineItems);
        $optionLineItem = $optionLineItems->first();
        static::assertInstanceOf(LineItem::class, $optionLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $optionLineItem->getPrice());
        static::assertSame(100.0, $optionLineItem->getPrice()->getTotalPrice());
        $optionValueLineItems = $optionLineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        static::assertCount(1, $optionValueLineItems);
        $optionValueLineItem = $optionValueLineItems->first();
        static::assertInstanceOf(LineItem::class, $optionValueLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $optionValueLineItem->getPrice());
        static::assertSame(100.0, $optionValueLineItem->getPrice()->getTotalPrice());
    }

    /**
     * @dataProvider oneTimeOptionProvider
     */
    public function testProcessPercentageCalculateOptionPrice(bool $isOneTime): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL
        );

        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();
        $configurationHash = Uuid::randomHex();

        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );

        $priceDefinition = new QuantityPriceDefinition(8.23, new TaxRuleCollection(), 100);

        $productLineItem->setPriceDefinition($priceDefinition);
        $customProductsTemplateLineItem->addChild($productLineItem);

        $customizedProductsOptionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            Uuid::randomHex(),
            $isOneTime ? 1 : 100,
        );
        $customizedProductsOptionLineItem->setPayload([
            'value' => 'on',
            'type' => 'checkox',
            'isOneTimeSurcharge' => $isOneTime,
            'productNumber' => '*',
        ]);

        $optionPriceDefinition = new PercentagePriceDefinition(18);

        $customizedProductsOptionLineItem->setPriceDefinition($optionPriceDefinition);
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $promotionItem = new LineItem(Uuid::randomHex(), LineItem::PROMOTION_LINE_ITEM_TYPE, 'test');
        $promotionItem->setPriceDefinition(new PercentagePriceDefinition(-10));
        $promotionItem->setLabel('Promotion');
        $promotionItem->setDescription('Promotion');
        $promotionItem->setGood(false);
        $promotionItem->setRemovable(true);
        $promotionItem->setPayload([
            'discountScope' => PromotionDiscountEntity::SCOPE_CART,
            'discountType' => PromotionDiscountEntity::TYPE_PERCENTAGE,
            'exclusions' => [],
            'promotionId' => Uuid::randomHex(),
            'code' => 'test',
            'promotionCodeType' => 'fixed',
            'preventCombination' => false,
        ]);

        $cart->add($promotionItem);

        $this->processor->process(
            $toCalculate->getData(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $this->calculateAmount($salesChannelContext, $toCalculate);

        $toCalculate->getData()->set(PromotionProcessor::DATA_KEY, new LineItemCollection([$promotionItem]));

        $this->getContainer()->get(PromotionProcessor::class)->process(
            $toCalculate->getData(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(2, $lineItemCollection);

        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);

        $optionLineItems = $lineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        static::assertCount(1, $optionLineItems);

        $optionLineItem = $optionLineItems->first();
        static::assertInstanceOf(LineItem::class, $optionLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $optionLineItem->getPrice());

        if ($isOneTime) {
            static::assertSame(1.48, $optionLineItem->getPrice()->getTotalPrice());
        } else {
            static::assertSame(148.0, $optionLineItem->getPrice()->getTotalPrice());
        }

        $discountLineItem = $lineItemCollection->filterType(LineItem::PROMOTION_LINE_ITEM_TYPE)->first();
        static::assertInstanceOf(LineItem::class, $discountLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $discountLineItem->getPrice());

        if ($isOneTime) {
            static::assertSame(-82.45, $discountLineItem->getPrice()->getTotalPrice());
        } else {
            static::assertSame(-97.1, $discountLineItem->getPrice()->getTotalPrice());
        }
    }

    /**
     * @dataProvider oneTimeOptionProvider
     */
    public function testCheckoutWillSaveCorrectCustomProductsUnitPrice(bool $isOneTime = true): void
    {
        $customerId = $this->createCustomer();

        $taxCriteria = new Criteria();
        $taxCriteria->addFilter(new EqualsFilter('taxRate', 19));
        $taxCriteria->setLimit(1);

        $taxId = $this->getContainer()->get('tax.repository')->searchIds($taxCriteria, Context::createDefaultContext())->firstId();
        static::assertNotNull($taxId);

        $this->getContainer()->get('swag_customized_products_template.repository')->create([
            [
                'id' => $this->ids->get('template'),
                'active' => true,
                'internalName' => 'test',
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => 'test',
                    ],
                ],
                'options' => [
                    [
                        'id' => $this->ids->get('option'),
                        'type' => Checkbox::NAME,
                        'required' => false,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'option',
                            ],
                        ],
                        'oneTimeSurcharge' => $isOneTime,
                        'percentageSurcharge' => 0,
                        'relativeSurcharge' => false,
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'gross' => 100,
                                'net' => 84.033613445378,
                                'linked' => true,
                            ],
                        ],
                        'taxId' => $taxId,
                    ],
                ],
            ],
        ], Context::createDefaultContext());

        $this->getContainer()->get('product.repository')->create([
            (new ProductBuilder($this->ids, 'p1'))
                ->price(10, 8.4033613445378)
                ->visibility()
                ->add(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN, [
                    'id' => $this->ids->get('template'),
                ])
                ->build(),
        ], Context::createDefaultContext());

        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(
            self::SALES_CHANNEL_TOKEN,
            TestDefaults::SALES_CHANNEL,
            [
                'customerId' => $customerId,
            ]
        );

        $request = new Request();
        $request->request->set(CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER, [
            'id' => $this->ids->get('template'),
            'options' => [
                $this->ids->get('option') => [
                    'value' => 'on',
                ],
            ],
        ]);
        $request->request->set('lineItems', [
            Uuid::randomHex() => [
                'id' => $this->ids->get('p1'),
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                'referencedId' => $this->ids->get('p1'),
                'quantity' => 100,
                'stackable' => true,
                'removable' => true,
            ],
        ]);

        $this->getContainer()->get(AddCustomizedProductsToCartRoute::class)->add(
            new RequestDataBag($request->request->all()),
            $request,
            $salesChannelContext,
            $cart
        );

        $orderResponse = $this->getContainer()->get(CartOrderRoute::class)->order($cart, $salesChannelContext, new RequestDataBag());

        $orderLineItems = $orderResponse->getOrder()->getLineItems();

        $cusLineItem = $orderLineItems?->filterByType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE)->first();
        static::assertInstanceOf(OrderLineItemEntity::class, $cusLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $cusLineItem->getPrice());

        if ($isOneTime) {
            static::assertSame(11.0, $cusLineItem->getUnitPrice());
            static::assertSame(1100.0, $cusLineItem->getTotalPrice());
        } else {
            static::assertSame(110.0, $cusLineItem->getUnitPrice());
            static::assertSame(11000.0, $cusLineItem->getTotalPrice());
        }
    }


    /**
     * @return iterable<array-key, array{bool}>
     */
    public static function oneTimeOptionProvider(): iterable
    {
        yield 'test one time surcharge' => [true];
        yield 'test surcharge' => [false];
    }

    private function calculateAmount(SalesChannelContext $context, Cart $cart): void
    {
        $amount = $this->getContainer()->get(AmountCalculator::class)->calculate(
            $cart->getLineItems()->getPrices(),
            $cart->getDeliveries()->getShippingCosts(),
            $context
        );

        $cart->setPrice($amount);
    }

    /**
     * @param array<string, string> $options
     */
    private function createCustomer(array $options = []): string
    {
        $customerId = Uuid::randomHex();
        $addressId = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'number' => '1337',
            'salutationId' => $this->getValidSalutationId(),
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'customerNumber' => '1337',
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'email' => $customerId . '@example.com',
            'password' => 'shopware',
            'defaultPaymentMethodId' => $this->getAvailablePaymentMethod()->getId(),
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'salesChannelId' => TestDefaults::SALES_CHANNEL,
            'defaultBillingAddressId' => $addressId,
            'defaultShippingAddressId' => $addressId,
            'addresses' => [
                [
                    'id' => $addressId,
                    'customerId' => $customerId,
                    'countryId' => $this->getValidCountryId(),
                    'salutationId' => $this->getValidSalutationId(),
                    'firstName' => 'Max',
                    'lastName' => 'Mustermann',
                    'street' => 'Ebbinghoff 10',
                    'zipcode' => '48624',
                    'city' => 'Schöppingen',
                ],
            ],
        ];

        $customer = \array_merge($customer, $options);

        $this->getContainer()->get('customer.repository')->upsert([$customer], Context::createDefaultContext());

        return $customerId;
    }
}
