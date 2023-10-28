<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\AbstractPriceDetailRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CustomizedProductPriceDetailController extends StorefrontController
{
    public function __construct(private readonly AbstractPriceDetailRoute $priceDetailRoute)
    {
    }

    /**
     * @Route(
     *     "/customized-products/price-detail-box",
     *     name="frontend.customized-products.price-detail-box",
     *     options={"seo"="false"},
     *     methods={"POST"},
     *     defaults={"XmlHttpRequest"=true}
     * )
     */
    public function priceDetailBox(Request $request, Cart $cart, SalesChannelContext $context): Response
    {
        $priceDetails = $this->priceDetailRoute->priceDetail($request, $context, $cart->getRuleIds());

        return $this->renderStorefront(
            '@SwagCustomizedProducts/storefront/component/customized-products/_include/price-detail-box.html.twig',
            [
                'priceDetails' => $priceDetails,
            ]
        );
    }
}
