<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Commercial\Subscription\Framework\SubscriptionFrameworkException;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeCartMergeEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\CartCreatedEvent;
use Shopware\Core\Checkout\Cart\Event\CartDeletedEvent;
use Shopware\Core\Checkout\Cart\Event\CartLoadedEvent;
use Shopware\Core\Checkout\Cart\Event\CartMergedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CartVerifyPersistEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\Event\LineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextCreatedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextRestoredEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextRestorerOrderCriteriaEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoadedEvent;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class SubscriptionEventRegistry
{
    /**
     * @var array<string, class-string<Event|ShopwareEvent>>
     */
    private array $events = [];

    /**
     * @internal
     */
    public function __construct()
    {
        $this->set(AfterLineItemAddedEvent::class);
        $this->set(AfterLineItemRemovedEvent::class);
        $this->set(AfterLineItemQuantityChangedEvent::class);
        $this->set(BeforeLineItemAddedEvent::class);
        $this->set(BeforeLineItemRemovedEvent::class);
        $this->set(BeforeLineItemQuantityChangedEvent::class);
        $this->set(BeforeCartMergeEvent::class);
        $this->set(CartCreatedEvent::class);
        $this->set(CartConvertedEvent::class);
        $this->set(CartDeletedEvent::class);
        $this->set(CartLoadedEvent::class);
        $this->set(CartMergedEvent::class);
        $this->set(CartSavedEvent::class);
        $this->set(CartVerifyPersistEvent::class);
        $this->set(CheckoutCartPageLoadedEvent::class);
        $this->set(CheckoutConfirmPageLoadedEvent::class);
        $this->set(CheckoutOrderPlacedCriteriaEvent::class);
        $this->set(CheckoutOrderPlacedEvent::class);
        $this->set(CheckoutRegisterPageLoadedEvent::class);
        $this->set(LineItemRemovedEvent::class);
        $this->set(SalesChannelContextCreatedEvent::class);
        $this->set(SalesChannelContextResolvedEvent::class);
        $this->set(SalesChannelContextRestoredEvent::class);
        $this->set(SalesChannelContextRestorerOrderCriteriaEvent::class);
        $this->set(OffcanvasCartPageLoadedEvent::class);
    }

    /**
     * @return array<string, class-string<Event|ShopwareEvent>>
     */
    public function all(): array
    {
        return $this->events;
    }

    public function has(string $key): bool
    {
        return \array_key_exists(self::prefixedEventName($key), $this->events);
    }

    /**
     * @param class-string<Event|ShopwareEvent> $class
     */
    public function set(string $class, ?string $eventName = null): void
    {
        if (!\is_a($class, Event::class, true) && !\is_a($class, ShopwareEvent::class, true)) {
            throw SubscriptionFrameworkException::invalidEventClass($class);
        }

        if (!$eventName) {
            $eventName = $class;
        }

        if (!\str_starts_with($eventName, SubscriptionEvents::SUBSCRIPTION_EVENT_PREFIX)) {
            $eventName = self::prefixedEventName($eventName);
        }

        $this->events[$eventName] = $class;
    }

    public static function prefixedEventName(string $name): string
    {
        return SubscriptionEvents::SUBSCRIPTION_EVENT_PREFIX . $name;
    }
}
