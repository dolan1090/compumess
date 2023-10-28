<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Aggregate\TemplateConfiguration\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service\TemplateConfigurationService;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service\TemplateConfigurationServiceInterface;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateConfigurationServiceTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var TemplateConfigurationServiceInterface
     */
    private $templateConfigurationService;

    protected function setUp(): void
    {
        $this->templateConfigurationService = $this->getContainer()->get(TemplateConfigurationService::class);
    }

    public function testGetTemplateConfiguration(): void
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $optionValue1Id = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'type' => Select::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'example-option',
                            ],
                        ],
                        'values' => [
                            [
                                'id' => $optionValue1Id,
                                'position' => 1,
                                'translations' => [
                                    Defaults::LANGUAGE_SYSTEM => [
                                        'displayName' => 'example-option-value1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            Uuid::randomHex()
        );

        $optionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $optionId
        );
        $optionLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
                $optionValue1Id
            )
        );
        $optionLineItem->setPayloadValue('value', $optionValue1Id);
        $optionLineItem->setPayloadValue('type', Select::NAME);

        $templateLineItem->addChild($optionLineItem);

        $configurationEntity = $this->templateConfigurationService->getTemplateConfiguration(
            $templateLineItem,
            Uuid::randomHex(),
            $context
        );

        static::assertNotNull($configurationEntity);
        $configArray = $configurationEntity->getConfiguration();
        static::assertCount(2, $configArray);
        static::assertArrayHasKey('quantity', $configArray);
        static::assertSame(1, $configArray['quantity']);
        static::assertArrayHasKey($optionId, $configArray);
        static::assertIsArray($configArray[$optionId]);
        static::assertArrayHasKey('type', $configArray[$optionId]);
        static::assertSame(Select::NAME, $configArray[$optionId]['type']);
        static::assertArrayHasKey('value', $configArray[$optionId]);
        static::assertIsArray($configArray[$optionId]['value']);
        static::assertCount(1, $configArray[$optionId]['value']);
        static::assertSame($optionValue1Id, $configArray[$optionId]['value'][0]);
    }

    public function testIsConfigurationFullyRestorable(): void
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();
        $option1Id = Uuid::randomHex();
        $option2Id = Uuid::randomHex();
        $optionValue1Id = Uuid::randomHex();
        $optionValue2Id = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $option1Id,
                        'type' => Select::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'example-option',
                            ],
                        ],
                        'values' => [
                            [
                                'id' => $optionValue1Id,
                                'position' => 1,
                                'translations' => [
                                    Defaults::LANGUAGE_SYSTEM => [
                                        'displayName' => 'example-option-value1',
                                    ],
                                ],
                            ],
                            [
                                'id' => $optionValue2Id,
                                'position' => 2,
                                'translations' => [
                                    Defaults::LANGUAGE_SYSTEM => [
                                        'displayName' => 'example-option-value2',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => $option2Id,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'example-option',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            Uuid::randomHex()
        );

        $optionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $option1Id
        );
        $optionLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
                $optionValue1Id
            )
        );
        $optionLineItem->setPayloadValue('value', $optionValue1Id);
        $optionLineItem->setPayloadValue('type', Select::NAME);

        $templateLineItem->addChild($optionLineItem);

        $configurationEntity = $this->templateConfigurationService->getTemplateConfiguration(
            $templateLineItem,
            Uuid::randomHex(),
            $context
        );
        static::assertNotNull($configurationEntity);

        static::assertTrue(
            $this->templateConfigurationService->isConfigurationFullyRestorable($configurationEntity, $context)
        );

        // Remove a saved option value and expect the configuration to not be fully restorable
        /** @var EntityRepository $optionValueRepository */
        $optionValueRepository = $this->getContainer()->get(
            \sprintf('%s.repository', TemplateOptionValueDefinition::ENTITY_NAME)
        );
        $optionValueRepository->delete([['id' => $optionValue1Id]], $context);
        static::assertFalse(
            $this->templateConfigurationService->isConfigurationFullyRestorable($configurationEntity, $context)
        );

        // No option value left so this option should not be fully restorable
        $optionValueRepository->delete([['id' => $optionValue2Id]], $context);
        static::assertFalse(
            $this->templateConfigurationService->isConfigurationFullyRestorable($configurationEntity, $context)
        );

        // Remove a saved option and expect the configuration to not be fully restorable
        /** @var EntityRepository $optionRepository */
        $optionRepository = $this->getContainer()->get(
            \sprintf('%s.repository', TemplateOptionDefinition::ENTITY_NAME)
        );
        $optionRepository->delete([['id' => $option1Id]], $context);
        static::assertFalse(
            $this->templateConfigurationService->isConfigurationFullyRestorable($configurationEntity, $context)
        );

        // No options left so this configuration is not fully restorable
        $optionRepository->delete([['id' => $option2Id]], $context);
        static::assertFalse(
            $this->templateConfigurationService->isConfigurationFullyRestorable($configurationEntity, $context)
        );
    }
}
