<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Pagelet\Quickview;

use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class QuickviewPageletLoader implements QuickviewPageletLoaderInterface
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected AbstractProductDetailRoute $productDetailRoute,
        protected ProductReviewLoader $productReviewLoader,
        protected ProductPageConfiguratorLoader $productPageConfiguratorLoader,
        private readonly SalesChannelRepository $productRepository
    ) {
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext): QuickviewPageletInterface
    {
        $criteria = (new Criteria())
            ->addAssociation('manufacturer.media')
            ->addAssociation('options.group')
            ->addAssociation('properties.group')
            ->addAssociation('mainCategories.category')
            ->addAssociation('swagCustomizedProductsTemplate.options');

        $criteria
            ->getAssociation('media')
            ->addSorting(new FieldSorting('position'));

        $response = $this->productDetailRoute->load(
            $this->getProductId($request, $salesChannelContext),
            $request,
            $salesChannelContext,
            $criteria
        );

        $product = $response->getProduct();

        $listingProductId = $request->attributes->get('productId');
        $reviews = $this->productReviewLoader->load($request, $salesChannelContext);
        $configuratorSettings = $this->productPageConfiguratorLoader->load($product, $salesChannelContext);

        $pagelet = new QuickviewPagelet($product, $listingProductId, $reviews, $configuratorSettings);

        $this->eventDispatcher->dispatch(new QuickviewPageletLoadedEvent($pagelet, $salesChannelContext, $request));

        return $pagelet;
    }

    protected function getProductId(Request $request, SalesChannelContext $salesChannelContext): string
    {
        $productId = $request->attributes->get('productId');

        if (!$productId) {
            throw RoutingException::missingRequestParameter('productId', '/productId');
        }

        return $this->findBestVariant($productId, $salesChannelContext);
    }

    private function findBestVariant(string $productId, SalesChannelContext $salesChannelContext): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->addSorting(new FieldSorting('product.price'))
            ->setLimit(1);

        $variantId = $this->productRepository->searchIds($criteria, $salesChannelContext)->firstId();

        return $variantId ?? $productId;
    }
}
