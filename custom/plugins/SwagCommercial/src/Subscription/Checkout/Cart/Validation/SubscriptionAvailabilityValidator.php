<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Validation;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\Error\SubscriptionAvailabilityError;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class SubscriptionAvailabilityValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        /** @var SubscriptionContextStruct $struct */
        $struct = $context->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);
        if ($struct === null) {
            return;
        }

        $plan = $struct->getPlan();
        if (($plan->getAvailabilityRuleId() !== null && !\in_array($plan->getAvailabilityRuleId(), $context->getRuleIds(), true)) || !$plan->isActive()) {
            $errors->add(new SubscriptionAvailabilityError(
                SubscriptionAvailabilityError::PLAN,
                $plan->getId()
            ));
        }

        $interval = $struct->getInterval();
        if (($interval->getAvailabilityRuleId() !== null && !\in_array($interval->getAvailabilityRuleId(), $context->getRuleIds(), true)) || !$interval->isActive()) {
            $errors->add(new SubscriptionAvailabilityError(
                SubscriptionAvailabilityError::INTERVAL,
                $interval->getId()
            ));
        }
    }
}
