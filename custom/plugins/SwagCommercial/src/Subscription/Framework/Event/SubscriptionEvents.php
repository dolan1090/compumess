<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('checkout')]
final class SubscriptionEvents
{
    final public const SUBSCRIPTION_EVENT_PREFIX = 'subscription.';

    final public const SUBSCRIPTION_CART_CONVERTED = self::SUBSCRIPTION_EVENT_PREFIX . CartConvertedEvent::class;

    final public const SUBSCRIPTION_CART_SAVED = self::SUBSCRIPTION_EVENT_PREFIX . CartSavedEvent::class;

    final public const SUBSCRIPTION_CART_VERIFY_PERSIST = self::SUBSCRIPTION_EVENT_PREFIX . CartVerifyPersistEvent::class;

    final public const SUBSCRIPTION_BEFORE_LINE_ITEM_ADDED = self::SUBSCRIPTION_EVENT_PREFIX . BeforeLineItemAddedEvent::class;

    private function __construct()
    {
    }
}
