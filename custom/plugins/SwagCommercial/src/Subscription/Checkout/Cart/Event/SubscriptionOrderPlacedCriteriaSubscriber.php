<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Event;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionOrderPlacedCriteriaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['subscription.' . CheckoutOrderPlacedCriteriaEvent::class => 'onOrderPlacedCriteria'];
    }

    public function onOrderPlacedCriteria(CheckoutOrderPlacedCriteriaEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('subscription.subscriptionCustomer');
        $criteria->addAssociation('subscription.addresses');
        $criteria->addAssociation('subscription.paymentMethod');
        $criteria->addAssociation('subscription.shippingMethod');
    }
}
