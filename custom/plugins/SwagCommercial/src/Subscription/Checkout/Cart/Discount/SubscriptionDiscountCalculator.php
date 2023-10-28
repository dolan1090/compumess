<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Discount;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionDiscountCalculator
{
    public function __construct(
        private readonly PercentagePriceCalculator $percentagePriceCalculator,
    ) {
    }

    public function calculateProduct(SubscriptionPlanEntity $plan, SalesChannelProductEntity $product, SalesChannelContext $context): CalculatedPrice
    {
        $discountPercentage = 100 - \abs($plan->getDiscountPercentage());
        $prices = new PriceCollection([$product->getCalculatedPrice()]);

        return $this->percentagePriceCalculator->calculate($discountPercentage, $prices, $context);
    }

    public function calculateCart(SubscriptionPlanEntity $plan, Cart $cart, SalesChannelContext $context): CalculatedPrice
    {
        return $this->percentagePriceCalculator->calculate(
            -\abs($plan->getDiscountPercentage()),
            $cart->getLineItems()->filterGoods()->getPrices(),
            $context
        );
    }
}
