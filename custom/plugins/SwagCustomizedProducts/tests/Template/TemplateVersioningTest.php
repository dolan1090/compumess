<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateVersioningTest extends TestCase
{
    use ServicesTrait;

    private const OPTION_DISPLAY_NAME = 'Nice option display name';
    private const REPOSITORY_SUFFIX = '.repository';

    private EntityRepository $templateRepository;

    private EntityRepository $productRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepository $templateRepository */
        $templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
        $this->templateRepository = $templateRepository;
        /** @var EntityRepository $productRepository */
        $productRepository = $container->get(ProductDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
        $this->productRepository = $productRepository;
    }

    public function testCreateTemplate(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();

        $optionData = [
            'id' => $optionId,
            'templateId' => $templateId,
            'displayName' => self::OPTION_DISPLAY_NAME,
            'type' => TextField::NAME,
            'position' => 0,
            'typeProperties' => [],
        ];

        $context = Context::createDefaultContext();
        $this->createTemplate(
            $templateId,
            $context
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $versionContext = $context->createWithVersionId($versionId);

        /** @var EntityRepository $optionRepository */
        $optionRepository = $this->getContainer()->get(TemplateOptionDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
        $optionRepository->create([$optionData], $versionContext);

        $this->templateRepository->merge($versionId, $versionContext);
    }

    public function testAddProductToTemplate(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context
        );

        $productId = Uuid::randomHex();
        $this->createProduct($productId, $context);

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $versionContext = $context->createWithVersionId($versionId);

        $this->productRepository->createVersion($productId, $versionContext, null, $versionContext->getVersionId());

        $this->templateRepository->update([
            [
                'id' => $templateId,
                'products' => [
                    ['id' => $productId],
                ],
            ],
        ], $versionContext);

        $this->templateRepository->merge($versionId, $versionContext);
    }

    public function testThatTemplateDoesVersionizeOption(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'templateId' => $templateId,
                        'displayName' => self::OPTION_DISPLAY_NAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'typeProperties' => [],
                    ],
                ],
            ]
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $context->createWithVersionId($versionId);

        $query = <<<SQL
SELECT `id` FROM `swag_customized_products_template_option`;
SQL;

        $optionIds = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchFirstColumn();

        static::assertNotNull($optionIds);
        static::assertCount(2, $optionIds);
    }

    public function testThatTemplateDoesVersionizeOptionSubEntities(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'templateId' => $templateId,
                        'displayName' => self::OPTION_DISPLAY_NAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'typeProperties' => [],
                        'prices' => [
                            [
                                'id' => Uuid::randomHex(),
                                'currencyId' => Defaults::CURRENCY,
                            ],
                        ],
                        'values' => [
                            [
                                'id' => Uuid::randomHex(),
                                'displayName' => self::OPTION_DISPLAY_NAME,
                                'position' => 0,
                                'prices' => [
                                    [
                                        'id' => Uuid::randomHex(),
                                        'currencyId' => Defaults::CURRENCY,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->templateRepository->createVersion($templateId, $context);

        $optionPriceIds = $this->getContainer()->get(Connection::class)->fetchAllAssociative(
            'SELECT `id` FROM `swag_customized_products_template_option_price`'
        );
        $optionValueIds = $this->getContainer()->get(Connection::class)->fetchAllAssociative(
            'SELECT `id` FROM `swag_customized_products_template_option_value`'
        );
        $optionValuePriceIds = $this->getContainer()->get(Connection::class)->fetchAllAssociative(
            'SELECT `id` FROM `swag_customized_products_template_option_value_price`'
        );

        static::assertNotNull($optionPriceIds);
        static::assertNotNull($optionValueIds);
        static::assertNotNull($optionValuePriceIds);
        static::assertCount(2, $optionPriceIds);
        static::assertCount(2, $optionValueIds);
        static::assertCount(2, $optionValuePriceIds);
    }

    public function testThatTemplateDoesntVersionizeProducts(): never
    {
        static::markTestSkipped('Currently skipped, core needs to be fixed first');
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $productId = Uuid::randomHex();
        $this->createProduct($productId, $context);

        $this->createTemplate(
            $templateId,
            $context,
            [
                'products' => [
                    ['id' => $productId],
                ],
            ]
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $context->createWithVersionId($versionId);

        $query = <<<SQL
SELECT `id` FROM `product`;
SQL;

        $productIds = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchFirstColumn();

        static::assertNotNull($productIds);
        static::assertCount(2, $productIds);
    }

    public function testThatTemplateDoesntDeleteProductAssociations(): never
    {
        static::markTestSkipped('Currently skipped, core needs to be fixed first');
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $productId = Uuid::randomHex();
        $this->createProduct($productId, $context);

        $this->createTemplate(
            $templateId,
            $context,
            [
                'products' => [
                    ['id' => $productId],
                ],
            ]
        );

        $versionContext = $context->createWithVersionId(
            $this->templateRepository->createVersion($templateId, $context)
        );
        $this->templateRepository->update([
            [
                'id' => $templateId,
                'products' => [
                    $productId => [
                        Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN => null,
                    ],
                ],
            ],
        ], $versionContext);

        $query = <<<SQL
SELECT `swag_customized_products_template_id` FROM `product`;
SQL;

        $id = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchOne();

        static::assertNull($id);
    }

    public function testThatProductTemplateAssociationDoenstGetUnset(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context
        );

        $productId = Uuid::randomHex();
        $this->createProduct($productId, $context);

        // Assign template to product
        $this->productRepository->update([
            [
                'id' => $productId,
                'swagCustomizedProductsTemplateId' => $templateId,
            ],
        ], $context);

        $this->assertProductTemplateAssociation($productId, $context, $templateId);

        $this->templateRepository->update([
            [
                'id' => $templateId,
                'internalName' => 'Foo Bar',
            ],
        ], $context);

        $this->assertProductTemplateAssociation($productId, $context, $templateId);
    }

    private function assertProductTemplateAssociation(string $productId, Context $context, string $templateId): void
    {
        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search(new Criteria([$productId]), $context)->first();
        static::assertNotNull($product);
        $extensions = $product->getExtensions();
        static::assertArrayHasKey('foreignKeys', $extensions);

        /** @var ArrayStruct<string, mixed>|null $foreignKeys */
        $foreignKeys = $extensions['foreignKeys'];

        static::assertNotNull($foreignKeys);
        static::assertTrue($foreignKeys->has('swagCustomizedProductsTemplateId'));
        static::assertSame($foreignKeys->get('swagCustomizedProductsTemplateId'), $templateId);
        static::assertTrue($foreignKeys->has('swagCustomizedProductsTemplateVersionId'));
        static::assertSame($foreignKeys->get('swagCustomizedProductsTemplateVersionId'), Defaults::LIVE_VERSION);
    }
}
