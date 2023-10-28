<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Pagelet\Quickview;

use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\FindVariant\FindProductVariantRoute;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class QuickviewVariantPageletLoader extends QuickviewPageletLoader
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        AbstractProductDetailRoute $productDetailRoute,
        ProductReviewLoader $productReviewLoader,
        ProductPageConfiguratorLoader $productPageConfiguratorLoader,
        SalesChannelRepository $productRepository,
        private readonly FindProductVariantRoute $findProductVariantRoute
    ) {
        parent::__construct($eventDispatcher, $productDetailRoute, $productReviewLoader, $productPageConfiguratorLoader, $productRepository);
    }

    protected function getProductId(Request $request, SalesChannelContext $salesChannelContext): string
    {
        $parentId = $request->query->getAlnum('parentId');
        $options = (string) $request->query->get('options');
        $request->query->set('options', \json_decode($options, true, 512, \JSON_THROW_ON_ERROR));

        $route = $this->findProductVariantRoute->load($parentId, $request, $salesChannelContext);

        return $route->getFoundCombination()->getVariantId();
    }
}
