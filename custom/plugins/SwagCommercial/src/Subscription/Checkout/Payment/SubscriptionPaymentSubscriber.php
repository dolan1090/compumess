<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Payment;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Payment\Event\FinalizePaymentOrderTransactionCriteriaEvent;
use Shopware\Core\Checkout\Payment\Event\PayPaymentOrderCriteriaEvent;
use Shopware\Core\Checkout\Payment\Event\RecurringPaymentOrderCriteriaEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionPaymentSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FinalizePaymentOrderTransactionCriteriaEvent::class => 'onOrderTransactionCriteriaLoaded',
            PayPaymentOrderCriteriaEvent::class => 'onOrderCriteriaLoaded',
            RecurringPaymentOrderCriteriaEvent::class => 'onOrderCriteriaLoaded',
        ];
    }

    public function onOrderTransactionCriteriaLoaded(FinalizePaymentOrderTransactionCriteriaEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('order.subscription');
    }

    public function onOrderCriteriaLoaded(PayPaymentOrderCriteriaEvent|RecurringPaymentOrderCriteriaEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('subscription');
    }
}
