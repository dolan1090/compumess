<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionStateHandler;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Extension\OrderSubscriptionExtension;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionEventRegistry;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionCheckoutOrderPlacedSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $subscriptionRepository,
        private readonly SubscriptionStateHandler $subscriptionStateHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [SubscriptionEventRegistry::prefixedEventName(CheckoutOrderPlacedEvent::class) => 'onSubscriptionOrderPlaced'];
    }

    public function onSubscriptionOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if (!$event->getOrder()->hasExtension(OrderSubscriptionExtension::SUBSCRIPTION_EXTENSION)) {
            return;
        }

        $subscription = $event->getOrder()->getExtension(OrderSubscriptionExtension::SUBSCRIPTION_EXTENSION);

        if (!$subscription instanceof SubscriptionEntity) {
            return;
        }

        $updatedExecutionCount = $subscription->getRemainingExecutionCount() - 1;

        if ($updatedExecutionCount <= 0) {
            if ($subscription->getStateMachineState()?->getTechnicalName() === SubscriptionStates::STATE_FLAGGED_CANCELLED) {
                $this->subscriptionStateHandler->cancel($subscription->getId(), $event->getContext());
            }

            $updatedExecutionCount = 0;
        }

        $update = ['id' => $subscription->getId(), 'remainingExecutionCount' => $updatedExecutionCount];
        $this->subscriptionRepository->update([$update], $event->getContext());
    }
}
