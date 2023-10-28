<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Content\Flow\Dispatching\Storer;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Framework\Event\SubscriptionAware;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Content\Flow\Dispatching\Storer\FlowStorer;
use Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class SubscriptionStorer extends FlowStorer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $subscriptionRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof SubscriptionAware || isset($stored[SubscriptionAware::SUBSCRIPTION_ID])) {
            return $stored;
        }

        $stored[SubscriptionAware::SUBSCRIPTION_ID] = $event->getSubscriptionId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(SubscriptionAware::SUBSCRIPTION_ID)) {
            return;
        }

        $storable->setData(SubscriptionAware::SUBSCRIPTION_ID, $storable->getStore(SubscriptionAware::SUBSCRIPTION_ID));

        $storable->lazy(
            SubscriptionAware::SUBSCRIPTION,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?SubscriptionEntity
    {
        $id = $storableFlow->getStore(SubscriptionAware::SUBSCRIPTION_ID);

        if (!$id || !\is_string($id)) {
            return null;
        }

        $criteria = new Criteria([$id]);

        return $this->loadSubscription($criteria, $storableFlow->getContext());
    }

    private function loadSubscription(Criteria $criteria, Context $context): ?SubscriptionEntity
    {
        $criteria->addAssociations([
            'subscriptionPlan',
            'subscriptionInterval',
            'subscriptionCustomer.salutation',
            'stateMachineState',
            'addresses.country',
            'billingAddress.country',
            'shippingAddress.country',
            'paymentMethod',
            'shippingMethod',
            'currency',
        ]);

        $event = new BeforeLoadStorableFlowDataEvent(
            SubscriptionDefinition::ENTITY_NAME,
            $criteria,
            $context,
        );

        $this->dispatcher->dispatch($event, $event->getName());

        /** @var SubscriptionEntity|null $subscription */
        $subscription = $this->subscriptionRepository
            ->search($criteria, $context)
            ->first();

        return $subscription;
    }
}
