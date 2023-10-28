<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\Demodata\Event\DemodataRequestCreatedEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionDemodataRequestCreatedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DemodataRequestCreatedEvent::class => 'onDemodataRequestCreated',
        ];
    }

    public function onDemodataRequestCreated(DemodataRequestCreatedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $request = $event->getRequest();
        $input = $event->getInput();

        $count = $input->getOption('subscription-intervals');
        if (\is_string($count) && (int) $count > 0) {
            $request->add(SubscriptionIntervalDefinition::class, (int) $count);
        }

        $count = $input->getOption('subscription-plans');
        if (\is_string($count) && (int) $count > 0) {
            $request->add(SubscriptionPlanDefinition::class, (int) $count);
        }

        $count = $input->getOption('subscriptions');
        if (\is_string($count) && (int) $count > 0) {
            $request->add(SubscriptionDefinition::class, (int) $count);
        }
    }
}
