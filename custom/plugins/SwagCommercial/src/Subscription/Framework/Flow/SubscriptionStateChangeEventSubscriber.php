<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Flow;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Order\Generation\SubscriptionTaskException;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionStateMachineStateChangedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionStateChangeEventSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $subscriptionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'state_machine.subscription.state_changed' => 'onSubscriptionStateChange',
        ];
    }

    public function onSubscriptionStateChange(StateMachineStateChangeEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $subscriptionId = $event->getTransition()->getEntityId();

        $criteria = new Criteria([$subscriptionId]);
        $criteria
            ->addAssociation('subscriptionPlan')
            ->addAssociation('subscriptionInterval')
            ->addAssociation('subscriptionCustomer');

        /** @var SubscriptionEntity|null $subscription */
        $subscription = $this->subscriptionRepository->search($criteria, $event->getContext())->first();

        if (!$subscription) {
            throw SubscriptionTaskException::subscriptionNotFound($subscriptionId);
        }

        $changeEvent = new SubscriptionStateMachineStateChangedEvent(
            $event->getStateEventName(),
            $event->getContext(),
            $subscription,
        );

        $this->eventDispatcher->dispatch($changeEvent, $event->getStateEventName());
    }
}
