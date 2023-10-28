<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\CartCreatedEvent;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsCartError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsPriceCalculationError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractAddCustomizedProductsToCartRoute;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class PriceDetailRoute extends AbstractPriceDetailRoute
{
    final public const PRICE_DETAIL_CALCULATION_EXTENSION_KEY = 'price-detail-calculation';

    public function __construct(
        private readonly AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PriceDetailService $priceDetailService
    ) {
    }

    public function getDecorated(): AbstractPriceDetailRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @inheritDoc
     *
     * @param array<string> $cartRuleIds
     *
     * @Route("/store-api/customized-products/price-detail", name="store-api.customized-products.price-detail", methods={"POST"})
     */
    public function priceDetail(Request $request, SalesChannelContext $context, array $cartRuleIds): PriceDetailResponse
    {
        $cart = new Cart(Uuid::randomHex());
        $cart->setRuleIds($cartRuleIds);

        $cart->addExtension(
            self::PRICE_DETAIL_CALCULATION_EXTENSION_KEY,
            new PriceDetailCalculationExtension()
        );

        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart));
        $this->addCustomizedProductsToCartRoute->add(new RequestDataBag($request->request->all()), $request, $context, $cart);

        $customizedProductLineItem = $cart->getLineItems()->first();
        if ($customizedProductLineItem === null) {
            $customizedProductCartError = $cart->getErrors()->filterInstance(SwagCustomizedProductsCartError::class)->first();

            throw $customizedProductCartError ?? new SwagCustomizedProductsPriceCalculationError();
        }

        $productPrice = $this->priceDetailService->getProductPrice($customizedProductLineItem);
        if ($productPrice === null) {
            throw new SwagCustomizedProductsPriceCalculationError($customizedProductLineItem->getId());
        }

        $customizedProductPrice = $customizedProductLineItem->getPrice();
        if ($customizedProductPrice === null) {
            throw new SwagCustomizedProductsPriceCalculationError($customizedProductLineItem->getId());
        }

        [$surcharges, $oneTimeSurcharges] = $this->priceDetailService->getSurcharges($customizedProductLineItem);

        $surchargesSubTotal = \array_reduce($surcharges, static fn (float $price, array $item): float => $price + $item['price'], 0);

        $oneTimeSurchargesSubTotal = \array_reduce($oneTimeSurcharges, static fn (float $price, array $item): float => $price + $item['price'], 0);

        return new PriceDetailResponse(
            $productPrice->getTotalPrice(),
            $customizedProductPrice->getTotalPrice(),
            $surchargesSubTotal,
            $oneTimeSurchargesSubTotal,
            $surcharges,
            $oneTimeSurcharges
        );
    }
}
