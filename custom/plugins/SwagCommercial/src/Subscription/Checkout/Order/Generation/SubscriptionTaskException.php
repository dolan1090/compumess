<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class SubscriptionTaskException extends HttpException
{
    public const SUBSCRIPTION_TASK_INVALID_ARGUMENT = 'CHECKOUT__SUBSCRIPTION_TASK_INVALID_ARGUMENT';

    public const SUBSCRIPTION_NOT_FOUND_CODE = 'CHECKOUT__SUBSCRIPTION_NOT_FOUND';

    public const SUBSCRIPTION_PAYMENT_METHOD_INVALID = 'CHECKOUT__SUBSCRIPTION_PAYMENT_METHOD_INVALID';

    public const SUBSCRIPTION_INVALID_ORDER = 'CHECKOUT__SUBSCRIPTION_INVALID_ORDER';

    public static function invalidArgument(string $key): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::SUBSCRIPTION_TASK_INVALID_ARGUMENT,
            'Missing data for subscription task: {{ key }}',
            ['key' => $key]
        );
    }

    public static function subscriptionNotFound(string $subscriptionId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::SUBSCRIPTION_NOT_FOUND_CODE,
            'Subscription with id {{ id }} not found.',
            ['id' => $subscriptionId]
        );
    }

    public static function paymentMethodInvalid(string $paymentMethodId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::SUBSCRIPTION_PAYMENT_METHOD_INVALID,
            'Payment method with id {{ id }} is invalid for handling recurring payments.',
            ['id' => $paymentMethodId]
        );
    }

    public static function invalidOrder(string $orderId): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::SUBSCRIPTION_INVALID_ORDER,
            'Order with id {{ id }} is invalid and cannot be used for recurring payments.',
            ['id' => $orderId]
        );
    }
}
