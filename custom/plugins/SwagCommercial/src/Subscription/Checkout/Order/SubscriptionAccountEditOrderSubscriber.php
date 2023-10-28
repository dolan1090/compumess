<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\Event\SalesChannelContextAssembledEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Event\RouteRequest\OrderRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionAccountEditOrderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderRouteRequestEvent::class => 'onOrderCriteria',
            SalesChannelContextAssembledEvent::class => 'onSalesChannelContextAssembled',
        ];
    }

    public function onOrderCriteria(OrderRouteRequestEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('subscription.subscriptionPlan');
        $criteria->addAssociation('subscription.subscriptionInterval');
    }

    public function onSalesChannelContextAssembled(SalesChannelContextAssembledEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $order = $event->getOrder();

        if (!$order->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return;
        }

        $subscription = $order->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        if (!$subscription instanceof SubscriptionEntity) {
            return;
        }

        $context = $event->getSalesChannelContext();

        if (!$subscription->getSubscriptionInterval() || !$subscription->getSubscriptionPlan()) {
            return;
        }

        $struct = new SubscriptionContextStruct(
            $context->getToken(),
            $subscription->getNextSchedule(),
            $subscription->getSubscriptionInterval(),
            $subscription->getSubscriptionPlan()
        );

        $context->addExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION, $struct);
    }
}
