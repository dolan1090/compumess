<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageOrderCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionCheckoutFinishSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutFinishPageOrderCriteriaEvent::class => 'onOrderCriteriaLoaded',
        ];
    }

    public function onOrderCriteriaLoaded(CheckoutFinishPageOrderCriteriaEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('subscription');
    }
}
