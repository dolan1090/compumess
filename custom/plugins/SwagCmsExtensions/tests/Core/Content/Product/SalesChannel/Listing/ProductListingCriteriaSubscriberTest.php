<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Test\Core\Content\Product\SalesChannel\Listing;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Test\Cart\Common\Generator;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\CmsExtensions\Core\Content\Product\SalesChannel\Listing\ProductListingCriteriaSubscriber;
use Swag\CmsExtensions\Storefront\Controller\QuickviewController;
use Symfony\Component\HttpFoundation\Request;

class ProductListingCriteriaSubscriberTest extends TestCase
{
    use KernelTestBehaviour;

    private ProductListingCriteriaSubscriber $productListingCriteriaSubscriber;

    protected function setUp(): void
    {
        $productListingCriteriaSubscriber = $this->getContainer()->get(ProductListingCriteriaSubscriber::class);
        static::assertInstanceOf(ProductListingCriteriaSubscriber::class, $productListingCriteriaSubscriber);
        $this->productListingCriteriaSubscriber = $productListingCriteriaSubscriber;
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                ProductEvents::PRODUCT_LISTING_CRITERIA => 'filterByParentId',
            ],
            ProductListingCriteriaSubscriber::getSubscribedEvents()
        );
    }

    public function testFilterByParentIdWithCorrectRoute(): void
    {
        $request = new Request();
        $request->attributes->set('_route', QuickviewController::QUICKVIEW_VARIANT_ROUTE);

        $filters = $this->getProductListingCriteriaEvent($request)->getCriteria()->getFilters();
        static::assertNotEmpty($filters);
        static::assertCount(1, $filters);

        $filter = $filters[0];
        static::assertInstanceOf(EqualsFilter::class, $filter);
        static::assertEquals('product.parentId', $filter->getField());
        static::assertNull($filter->getValue());
    }

    public function testFilterByParentIdWithWrongRoute(): void
    {
        $event = $this->getProductListingCriteriaEvent(new Request());
        $this->productListingCriteriaSubscriber->filterByParentId($event);

        $criteria = $event->getCriteria();
        static::assertEmpty($criteria->getFilters());
    }

    private function getProductListingCriteriaEvent(Request $request): ProductListingCriteriaEvent
    {
        $event = new ProductListingCriteriaEvent(
            $request,
            new Criteria(),
            Generator::createSalesChannelContext()
        );
        $this->productListingCriteriaSubscriber->filterByParentId($event);

        return $event;
    }
}
