<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Content\Product\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\SalesChannel\Detail\CachedProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Test\TestDefaults;
use Swag\CustomizedProducts\Core\Content\Product\SalesChannel\SalesChannelProductSubscriber;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueEntity;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelProductSubscriberTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var SalesChannelProductDefinition
     */
    private $salesChannelProductDefinition;

    private SalesChannelContext $salesChannelContext;

    /**
     * @var EntityRepository
     */
    private $productRepo;

    /**
     * @var CachedSalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var SalesChannelProductSubscriber
     */
    private $subscriber;

    /**
     * @var CachedProductDetailRoute
     */
    private $productDetailRoute;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );
        $this->salesChannelProductDefinition = $container->get(SalesChannelProductDefinition::class);
        $this->productRepo = $container->get('product.repository');
        $this->subscriber = $container->get(SalesChannelProductSubscriber::class);
        $this->productDetailRoute = $this->getContainer()->get(ProductDetailRoute::class);
    }

    public function testAddCustomizedProductsListingAssociations(): void
    {
        $criteria = new Criteria();
        $event = new ProductListingCriteriaEvent(
            new Request(),
            $criteria,
            $this->salesChannelContext
        );
        $this->subscriber->addCustomizedProductsListingAssociation($event);

        static::assertTrue(
            $criteria->hasAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)
        );
        $customizedTemplateCriteria = $criteria->getAssociation(
            Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN
        );
        static::assertTrue($customizedTemplateCriteria->hasAssociation('options'));
    }

    public function testIfSortingOfValuesAreCorrect(): void
    {
        $productUuid = Uuid::randomHex();

        $product = require __DIR__ . '/../../../../fixtures/custom_product_data.php';
        $this->productRepo->create([$product], $this->salesChannelContext->getContext());

        $this->salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );

        $criteria = (new Criteria([$productUuid]))
            ->addAssociation('swagCustomizedProductsTemplate.options.values');

        $criteria->getAssociation('swagCustomizedProductsTemplate.options.values')
            ->addSorting(new FieldSorting('position', 'ASC'));

        /** @var SalesChannelProductEntity $responseProduct */
        $responseProduct = $this->productDetailRoute->load(
            $productUuid,
            new Request(),
            $this->salesChannelContext,
            $criteria
        )->getProduct();

        static::assertTrue(
            $responseProduct->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)
        );
        /** @var TemplateEntity $template */
        $template = $responseProduct->getExtension(
            Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN
        );
        /** @var TemplateOptionCollection $optionCollection */
        $optionCollection = $template->getOptions();
        $options = \array_values($optionCollection->getElements());

        /** @var TemplateOptionValueEntity[] $values */
        $values = \array_values($options[0]->get('values')->getElements());

        static::assertSame(1, $values[0]->getPosition());
        static::assertSame(3, $values[1]->getPosition());
        static::assertSame(4, $values[2]->getPosition());
    }
}
