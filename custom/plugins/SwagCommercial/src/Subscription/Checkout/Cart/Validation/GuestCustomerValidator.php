<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Validation;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\Error\GuestCustomerError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class GuestCustomerValidator implements CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $customer = $context->getCustomer();
        if ($customer === null) {
            return;
        }

        if (!$customer->getGuest()) {
            return;
        }

        $errors->add(new GuestCustomerError());
    }
}
