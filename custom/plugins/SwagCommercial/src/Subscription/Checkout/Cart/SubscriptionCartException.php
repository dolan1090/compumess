<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart;

use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class SubscriptionCartException extends CartException
{
    public const TOKEN_NOT_FOUND_CODE = 'CHECKOUT__SUBSCRIPTION_CART_TOKEN_NOT_FOUND';
    public const CART_IS_NOT_SUBSCRIPTION_CART = 'CHECKOUT__CART_IS_NOT_SUBSCRIPTION_CART';
    public const PROMOTION_NOT_IMPLEMENTED = 'CHECKOUT__PROMOTION_NOT_IMPLEMENTED';

    public const CONVERSION_MISSING_DATA = 'CHECKOUT__CONVERSION_MISSING_DATA';
    public const PLAN_NOT_FOUND_CODE = 'CHECKOUT__PLAN_NOT_FOUND';
    public const INTERVAL_NOT_FOUND_CODE = 'CHECKOUT__INTERVAL_NOT_FOUND';

    public static function tokenNotFound(string $token): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::TOKEN_NOT_FOUND_CODE,
            'Subscription cart with token {{ token }} not found.',
            ['token' => $token]
        );
    }

    public static function isNotSubscriptionCart(): self
    {
        return new self(
            Response::HTTP_NOT_ACCEPTABLE,
            self::CART_IS_NOT_SUBSCRIPTION_CART,
            'Cart is not a subscription cart.'
        );
    }

    public static function promotionNotImplemented(): self
    {
        return new self(
            Response::HTTP_NOT_IMPLEMENTED,
            self::PROMOTION_NOT_IMPLEMENTED,
            'Promotion is not implemented for subscription cart.'
        );
    }

    public static function missingDataForConversion(string $data): self
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::CONVERSION_MISSING_DATA,
            'Subscription cannot be converted due to missing data: {{ data }}',
            ['data' => $data],
        );
    }

    public static function intervalNotFound(?string $id = null): self
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::INTERVAL_NOT_FOUND_CODE,
            'Subscription interval {{ id }} not found.',
            ['id' => $id],
        );
    }

    public static function planNotFound(?string $id = null): self
    {
        return new self(
            Response::HTTP_PRECONDITION_FAILED,
            self::PLAN_NOT_FOUND_CODE,
            'Subscription plan {{ id }} not found.',
            ['id' => $id],
        );
    }
}
