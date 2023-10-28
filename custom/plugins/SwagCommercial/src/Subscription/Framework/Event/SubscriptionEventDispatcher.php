<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[Package('checkout')]
class SubscriptionEventDispatcher implements EventDispatcherInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $decorated,
        private readonly SubscriptionEventRegistry $registry,
    ) {
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return $this->decorated->dispatch($event, $eventName);
        }

        if (!$eventName) {
            $eventName = $event::class;
        }

        if (!$event instanceof ShopwareSalesChannelEvent) {
            return $this->decorated->dispatch($event, $eventName);
        }

        if (!$event->getSalesChannelContext()->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return $this->decorated->dispatch($event, $eventName);
        }

        if (!$this->registry->has($eventName)) {
            return $this->decorated->dispatch($event, $eventName);
        }

        $eventName = SubscriptionEventRegistry::prefixedEventName($eventName);

        return $this->decorated->dispatch($event, $eventName);
    }

    /**
     * @param callable $listener can not use native type declaration @see https://github.com/symfony/symfony/issues/42283
     */
    public function addListener(string $eventName, $listener, int $priority = 0): void
    {
        /** @var callable(object): void $listener - Specify generic callback interface callers can provide more specific implementations */
        $this->decorated->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->decorated->addSubscriber($subscriber);
    }

    /**
     * @param callable $listener can not use native type declaration @see https://github.com/symfony/symfony/issues/42283
     */
    public function removeListener(string $eventName, $listener): void
    {
        /** @var callable(object): void $listener - Specify generic callback interface callers can provide more specific implementations */
        $this->decorated->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->decorated->removeSubscriber($subscriber);
    }

    public function getListeners(?string $eventName = null): array
    {
        return $this->decorated->getListeners($eventName);
    }

    /**
     * @param callable $listener can not use native type declaration @see https://github.com/symfony/symfony/issues/42283
     */
    public function getListenerPriority(string $eventName, $listener): ?int
    {
        /** @var callable(object): void $listener - Specify generic callback interface callers can provide more specific implementations */
        return $this->decorated->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return $this->decorated->hasListeners($eventName);
    }
}
