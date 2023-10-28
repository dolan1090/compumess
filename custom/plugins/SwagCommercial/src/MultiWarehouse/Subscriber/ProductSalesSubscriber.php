<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Order\ProductSalesUpdater;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductSalesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ProductSalesUpdater $salesUpdater
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'onOrderStateChanged',
        ];
    }

    public function onOrderStateChanged(StateMachineTransitionEvent $event): void
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return;
        }

        if ($event->getEntityName() !== OrderDefinition::ENTITY_NAME || $event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        if ($event->getToPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->salesUpdater->increaseSales($event->getEntityId(), $event->getContext());

            return;
        }

        if ($event->getFromPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->salesUpdater->decreaseSales($event->getEntityId(), $event->getContext());
        }
    }
}
