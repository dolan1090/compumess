<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CmsExtensions\Storefront\Pagelet\Quickview\QuickviewPageletLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class QuickviewController extends StorefrontController
{
    final public const QUICKVIEW_ROUTE = 'widgets.swag.cmsExtensions.quickview';
    final public const QUICKVIEW_VARIANT_ROUTE = 'widgets.swag.cmsExtensions.quickview.variant';

    public function __construct(
        private readonly QuickviewPageletLoaderInterface $quickviewPageletLoader,
        private readonly QuickviewPageletLoaderInterface $quickviewVariantPageletLoader
    ) {
    }

    #[Route(path: '/swag/cms-extensions/quickview/{productId}', name: 'widgets.swag.cmsExtensions.quickview', options: ['seo' => false], methods: ['GET'], defaults: ['productId' => null, 'XmlHttpRequest' => true])]
    public function quickview(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        return $this->renderStorefront(
            '@SwagCmsExtensions/storefront/component/quickview/index.html.twig',
            ['page' => $this->quickviewPageletLoader->load($request, $salesChannelContext)]
        );
    }

    #[Route(path: '/swag/cms-extensions/quickview/variant/{productId}', name: 'widgets.swag.cmsExtensions.quickview.variant', options: ['seo' => false], methods: ['GET'], defaults: ['productId' => null, 'XmlHttpRequest' => true])]
    public function quickviewVariant(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        return $this->renderStorefront(
            '@SwagCmsExtensions/storefront/component/quickview/index.html.twig',
            ['page' => $this->quickviewVariantPageletLoader->load($request, $salesChannelContext)]
        );
    }
}
