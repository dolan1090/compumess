<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Discount;

use Shopware\Commercial\Subscription\Checkout\Cart\SubscriptionCartException;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Package('checkout')]
class SubscriptionDiscountProcessor implements CartProcessorInterface
{
    public const LINE_ITEM_TYPE = 'subscriptionDiscount';

    /**
     * @internal
     */
    public function __construct(
        private readonly SubscriptionDiscountCalculator $subscriptionDiscountCalculator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $plan = $context->getExtensionOfType(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION, SubscriptionContextStruct::class)?->getPlan();

        if (!$plan) {
            throw SubscriptionCartException::isNotSubscriptionCart();
        }

        if ($toCalculate->getLineItems()->filterType(self::LINE_ITEM_TYPE)->count() !== 0 || $plan->getDiscountPercentage() === 0.0) {
            return;
        }

        $label = $this->translator->trans(
            'commercial.subscriptions.lineItem.discountLabel',
            ['%discount%' => \round($plan->getDiscountPercentage(), 2)]
        );

        $price = $this->subscriptionDiscountCalculator->calculateCart($plan, $original, $context);

        if ($price->getTotalPrice() === 0.0) {
            return;
        }

        $discountLineItem = (new LineItem($plan->getId(), self::LINE_ITEM_TYPE))
            ->setLabel($label)
            ->setPrice($price)
            ->setGood(false)
            ->setStackable(false)
            ->setRemovable(false);

        $toCalculate->add($discountLineItem);
    }
}
