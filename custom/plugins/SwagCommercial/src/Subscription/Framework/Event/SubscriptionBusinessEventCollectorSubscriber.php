<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionBusinessEventCollectorSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly BusinessEventCollector $businessEventCollector,
        private readonly SubscriptionEventRegistry $subscriptionEventRegistry
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BusinessEventCollectorEvent::NAME => ['onEventsCollected'],
        ];
    }

    public function onEventsCollected(BusinessEventCollectorEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $subscriptionEvents = $this->subscriptionEventRegistry->all();

        // add all bubbled subscription events, which are flow aware to the business event collector
        // so they are properly recognized in the flow builder
        foreach ($subscriptionEvents as $subscriptionEventClass) {
            if (!\is_a($subscriptionEventClass, FlowEventAware::class, true)) {
                continue;
            }

            $ref = new \ReflectionClass($subscriptionEventClass);
            $eventObj = $ref->newInstanceWithoutConstructor();

            $definition = $this->businessEventCollector->define($subscriptionEventClass, SubscriptionEventRegistry::prefixedEventName($eventObj->getName()));

            if (!$definition) {
                continue;
            }

            $event->getCollection()->add($definition);
        }

        $definition = $this->businessEventCollector->define(CheckoutSubscriptionPlacedEvent::class, CheckoutSubscriptionPlacedEvent::EVENT_NAME);

        if (!$definition) {
            return;
        }

        $event->getCollection()->add($definition);
    }
}
