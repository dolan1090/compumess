<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\Controller;

use Shopware\Commercial\CheckoutSweetener\SalesChannel\CheckoutSweetenerRoute;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @final
 *
 * @internal
 */
#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['storefront']])]
class CheckoutSweetenerController extends StorefrontController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CheckoutSweetenerRoute $checkoutSweetenerRoute
    ) {
    }

    #[Route(
        path: '/checkout/finish/generate-checkout-sweetener',
        name: 'commercial.storefront.generate_checkout-sweetener.get',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CHECKOUT_SWEETENER-8945908\')',
    )]
    public function generateSweetener(Request $request, SalesChannelContext $context): Response
    {
        return $this->checkoutSweetenerRoute->generate($request, $context);
    }
}
