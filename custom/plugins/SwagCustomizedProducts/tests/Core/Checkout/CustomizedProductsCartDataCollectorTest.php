<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\Test\TestDefaults;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class CustomizedProductsCartDataCollectorTest extends TestCase
{
    use ServicesTrait;

    private const TEMPLATE_NAME = 'tea-cup-template';

    private CustomizedProductsCartDataCollector $cartDataCollector;

    private CartService $cartService;

    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    private EntityRepository $templateRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        /** @var CustomizedProductsCartDataCollector $cartDataCollector */
        $cartDataCollector = $container->get(CustomizedProductsCartDataCollector::class);
        $this->cartDataCollector = $cartDataCollector;
        $this->cartService = $container->get(CartService::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        /** @var EntityRepository $templateRepository */
        $templateRepository = $container->get('swag_customized_products_template.repository');
        $this->templateRepository = $templateRepository;
    }

    public function testCollectWithCartBehaviourRecalculation(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $behavior = new CartBehavior([ProductCartProcessor::SKIP_PRODUCT_RECALCULATION => true]);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            $behavior
        );

        static::assertEmpty($cart->getErrors());
    }

    public function testCollectWithoutCustomProductsLineItems(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            LineITem::PRODUCT_LINE_ITEM_TYPE
        );
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        static::assertEmpty($cart->getErrors());
    }

    public function testCollectTemplateWithoutProductAddsErrorToCart(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithWrongReferencedIdAddsErrorToCart(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            Uuid::randomHex()
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductUnavailableIfProductEntityNotFound(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext()
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            Uuid::randomHex()
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductUnavailableIfProductEntityDoesntHaveTemplateExtension(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductDoesntContainAllRequiredOptionsIfNoOptionsExist(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithoutRequiredOptions(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        static::assertCount(0, $cart->getErrors());
    }

    public function dataCollectGeneratesConfiguration(): array
    {
        return [
            [
                [
                    [
                        'id' => Uuid::randomHex(),
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
                [
                    'on',
                ],
            ],
            [
                [
                    [
                        'id' => Uuid::randomHex(),
                        'type' => TextField::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
                [
                    'ExampleValue',
                ],
            ],
            [
                [
                    [
                        'id' => Uuid::randomHex(),
                        'type' => Textarea::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
                [
                    'ExampleValue',
                ],
            ],
            [
                [
                    [
                        'id' => Uuid::randomHex(),
                        'type' => HtmlEditor::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
                [
                    '<b>Example HTML editor text</b>',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCollectGeneratesConfiguration
     */
    public function testCollectGeneratesConfiguration(array $options, array $payloadValues): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => $options,
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        foreach ($options as $key => $option) {
            $optionLineItem = new LineItem(
                Uuid::randomHex(),
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
                $option['id']
            );

            $payloadValue = $payloadValues[$key];
            if ($payloadValue) {
                $optionLineItem->setPayloadValue('value', $payloadValue);
            }

            $templateLineItem->addChild($optionLineItem);
        }

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        // No errors should occur
        static::assertCount(0, $cart->getErrors());

        /** @var TemplateConfigurationEntity|null $configurationEntity */
        $configurationEntity = $templateLineItem->getExtension(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCT_CONFIGURATION_KEY
        );
        static::assertNotNull($configurationEntity);
        static::assertSame($templateId, $configurationEntity->getTemplateId());
        $configurationHash = $templateLineItem->getPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH
        );
        static::assertNotNull($configurationHash);
        static::assertSame($configurationHash, $configurationEntity->getHash());
        $configuration = $configurationEntity->getConfiguration();

        // Option count +1 because the quantity gets saved as well
        static::assertCount(\count($options) + 1, $configuration);

        foreach ($options as $option) {
            static::assertArrayHasKey($option['id'], $configuration);
            static::assertSame($configuration[$option['id']]['type'], $option['type']);
        }
    }

    public function testCollectWithoutMatchingRequiredOptionCount(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'type' => TextField::NAME,
                        'required' => true,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-option',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithoutRequiredOption(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $noneRequiredOptionId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'type' => TextField::NAME,
                        'required' => true,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                    [
                        'id' => $noneRequiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $productId = Uuid::randomHex();
        $this->createProduct(
            $templateId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $noneRequiredOptionId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithRequiredOption(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => TextField::NAME,
                        'required' => true,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild((new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $requiredOptionId
        ))->setPayloadValue('value', 'example-value'));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(0, $errorCollection);
    }

    public function testCollectNoneExistingOptionReferenceId(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild((new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            Uuid::randomHex()
        ))->setPayloadValue('value', 'on'));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithOptionMissingPayloadValue(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);

        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $requiredOptionId
        ));
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithOptionValues(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', TestDefaults::SALES_CHANNEL);

        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $optionValueId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                        'values' => [
                            [
                                'id' => $optionValueId,
                                'position' => 1,
                                'translations' => [
                                    Defaults::LANGUAGE_SYSTEM => [
                                        'displayName' => 'required-option-value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $productId = Uuid::randomHex();
        $this->createProduct(
            $productId,
            $context->getContext(),
            null,
            [
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $optionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $requiredOptionId
        );

        $optionLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
            $optionValueId
        ));

        $templateLineItem->addChild($optionLineItem);
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(0, $errorCollection);
        $optionValues = $cart->getLineItems()->filterFlatByType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        static::assertCount(1, $optionValues);
        $optionValue = $optionValues[0];
        static::assertInstanceOf(LineItem::class, $optionValue);
    }
}
