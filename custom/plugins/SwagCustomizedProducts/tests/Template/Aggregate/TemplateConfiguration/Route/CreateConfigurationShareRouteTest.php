<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Aggregate\TemplateConfiguration\Route;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\Test\TestDefaults;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Storefront\Page\Product\ProductPageSubscriber;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate\TemplateConfigurationShareDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route\AbstractCreateConfigurationShareRoute;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route\CreateConfigurationShareRoute;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;

class CreateConfigurationShareRouteTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var AbstractCreateConfigurationShareRoute
     */
    private $configurationShareRoute;

    /**
     * @var CachedSalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->configurationShareRoute = $container->get(CreateConfigurationShareRoute::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
    }

    public function testThatGetDecoratedThrowsDecorationPatternException(): void
    {
        $this->expectException(DecorationPatternException::class);
        $this->configurationShareRoute->getDecorated();
    }

    public function testCreateConfigurationShare(): void
    {
        $context = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );

        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $optionValue1Id = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
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
                                        'displayName' => 'example-option-value',
                                    ],
                                ],
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

        $request = new Request(
            [],
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                ],
                'lineItems' => [
                    [
                        // Product LineItem
                        'quantity' => 1,
                        'id' => $productId,
                    ],
                ],
                'absoluteBaseUrl' => 'https://example.shop',
            ]
        );
        $createShareResponse = $this->configurationShareRoute->createConfigurationShare($request, $context);
        static::assertNotNull($createShareResponse);
        $shareUrl = $createShareResponse->getShareUrl();
        static::assertNotEmpty($shareUrl);
        $matches = [];
        $pattern = \sprintf(
            '/(https:\/\/example\.shop\/).*(\?%s=)([0-9a-f]{32})/',
            ProductPageSubscriber::SHARE_CONFIGURATION_PARAMETER
        );

        \preg_match_all(
            $pattern,
            $shareUrl,
            $matches
        );
        static::assertNotEmpty($matches);
        // We expect 4 matches total. 1 for the whole string and the 3 groups
        static::assertCount(4, $matches);

        $shareId = $matches[3][0];
        static::assertTrue(Uuid::isValid($shareId));

        /** @var EntityRepository $shareRepository */
        $shareRepository = $this->getContainer()->get(
            \sprintf('%s.repository', TemplateConfigurationShareDefinition::ENTITY_NAME)
        );
        $shareId = $shareRepository->searchIds(new Criteria([$shareId]), $context->getContext())->firstId();
        static::assertNotNull($shareId);
    }
}
