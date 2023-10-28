<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SalesChannel;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SalesChannelSubscriptionPlanEntity extends SubscriptionPlanEntity
{
    private ?CalculatedPrice $discountPrice = null;

    public function getDiscountPrice(): ?CalculatedPrice
    {
        return $this->discountPrice;
    }

    public function setDiscountPrice(CalculatedPrice $discountPrice): void
    {
        $this->discountPrice = $discountPrice;
    }
}
