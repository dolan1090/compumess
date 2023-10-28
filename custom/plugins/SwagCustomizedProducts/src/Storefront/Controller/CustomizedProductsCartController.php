<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Promotion\Cart\Error\PromotionNotEligibleError;
use Shopware\Core\Checkout\Promotion\Cart\Error\PromotionNotFoundError;
use Shopware\Core\Checkout\Promotion\Cart\PromotionCartAddedInformationError;
use Shopware\Core\Checkout\Promotion\Cart\PromotionCartDeletedInformationError;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsCartError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractAddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractReOrderCustomizedProductsRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class CustomizedProductsCartController extends StorefrontController
{
    final public const ADD_TO_CART_IDENTIFIER = 'ADD_TO_CART_IDENTIFIER';
    final public const CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER = 'customized-products-template';
    final public const CUSTOMIZED_PRODUCTS_ADD_TO_CART_COUNT = 'customizedProductsAddToCartCount';

    public function __construct(
        private readonly AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute,
        private readonly AbstractReOrderCustomizedProductsRoute $reOrderCustomizedProductsRoute
    ) {
    }

    /**
     * @Route("/checkout/customized-products/add", name="frontend.checkout.customized-products.add", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function addCustomizedProduct(
        Cart $cart,
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response {
        $this->addCustomizedProductsToCartRoute->add($requestDataBag, $request, $salesChannelContext, $cart);

        return $this->finishAction($cart, $request);
    }

    /**
     * @Route("/checkout/customized-products/reorder/{orderId}", name="frontend.checkout.customized-products.reorder", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function reorderCustomizedProduct(
        string $orderId,
        Cart $originalCart,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): Response {
        $this->reOrderCustomizedProductsRoute->reOrder($orderId, $request, $salesChannelContext, $originalCart);

        return $this->finishAction($originalCart, $request);
    }

    private function finishAction(Cart $cart, Request $request): Response
    {
        $customizedProductCalculationErrors = $cart->getErrors()->filter(static fn (Error $error) => $error instanceof SwagCustomizedProductsCartError);

        if ($customizedProductCalculationErrors->count() > 0) {
            foreach ($customizedProductCalculationErrors as $error) {
                $this->addFlash('danger', $this->trans($error->getMessageKey()));
            }

            return $this->createActionResponse($request);
        }

        $promotionErrors = $cart->getErrors()->filter(static function (Error $error) {
            $promotionErrors = [
                PromotionNotEligibleError::class,
                PromotionNotFoundError::class,
                PromotionCartAddedInformationError::class,
                PromotionCartDeletedInformationError::class,
            ];

            return \in_array($error::class, $promotionErrors, true);
        });

        foreach ($promotionErrors as $error) {
            if ($error instanceof PromotionCartAddedInformationError
                    || $error instanceof PromotionCartDeletedInformationError
            ) {
                $this->addFlash(
                    'info',
                    $this->trans(
                        \sprintf('checkout.%s', $error->getMessageKey()),
                        [
                            '%name%' => $error->getParameters()['name'],
                        ]
                    )
                );

                continue;
            }

            $this->addFlash('info', $this->trans(\sprintf('checkout.%s', $error->getMessageKey())));
        }

        $this->addFlash('success', $this->trans('checkout.addToCartSuccess', [
            '%count%' => $request->attributes->getInt(self::CUSTOMIZED_PRODUCTS_ADD_TO_CART_COUNT, 1),
        ]));

        return $this->createActionResponse($request);
    }
}
