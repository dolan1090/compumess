<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnException;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Commercial\ReturnManagement\Event\OrderReturnCreatedEvent;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Shopware\Core\Framework\Event\BusinessEventDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class ReturnStateChangeEventListener implements EventSubscriberInterface
{
    final public const ORDER_RETURN_STATE_CHANGED_EVENT = 'state_machine.order_return.state_changed';
    private const STATE_ENTER_ORDER_LINE_ITEM = 'state_enter.order_line_item';
    private const STATE_LEAVE_ORDER_LINE_ITEM = 'state_leave.order_line_item';

    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    public function __construct(
        private readonly EntityRepository $orderReturnRepository,
        private readonly BusinessEventCollector $businessEventCollector,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::ORDER_RETURN_STATE_CHANGED_EVENT => 'onOrderReturnStateChanged',
            BusinessEventCollectorEvent::NAME => [['onStateEventsCollected', -1]],
        ];
    }

    public function onOrderReturnStateChanged(StateMachineStateChangeEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $returnId = $event->getTransition()->getEntityId();

        $criteria = new Criteria([$returnId]);
        $criteria->addAssociation('order');

        /** @var OrderReturnEntity|null $orderReturn */
        $orderReturn = $this->orderReturnRepository
            ->search($criteria, $event->getContext())->first();

        if ($orderReturn === null) {
            throw OrderReturnException::orderReturnNotFound($returnId);
        }

        $order = $orderReturn->getOrder();

        if ($order === null) {
            throw OrderException::orderNotFound($orderReturn->getOrderId());
        }

        $context = $this->createContext($order, $event->getContext());
        $this->dispatchEvent($event->getStateEventName(), $order, $context);
    }

    public function onStateEventsCollected(BusinessEventCollectorEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $collection = $event->getCollection();
        foreach ($collection as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }

            if (str_starts_with($key, self::STATE_ENTER_ORDER_LINE_ITEM) || str_starts_with($key, self::STATE_LEAVE_ORDER_LINE_ITEM)) {
                $collection->remove($key);
            }
        }

        /**
         * @var BusinessEventDefinition $returnCreatedBusinessEvent
         */
        $returnCreatedBusinessEvent = $this->businessEventCollector->define(OrderReturnCreatedEvent::class, OrderReturnCreatedEvent::EVENT_NAME);
        $collection->set(OrderReturnCreatedEvent::EVENT_NAME, $returnCreatedBusinessEvent);
    }

    private function createContext(OrderEntity $order, Context $context): Context
    {
        /** @var CashRoundingConfig $itemRounding */
        $itemRounding = $order->getItemRounding();

        $orderContext = new Context(
            $context->getSource(),
            $order->getRuleIds() ?? [],
            $order->getCurrencyId(),
            array_values(array_unique(array_merge([$order->getLanguageId()], $context->getLanguageIdChain()))),
            $context->getVersionId(),
            $order->getCurrencyFactor(),
            true,
            $order->getTaxStatus(),
            $itemRounding
        );

        $orderContext->addState(...$context->getStates());
        $orderContext->addExtensions($context->getExtensions());

        return $orderContext;
    }

    private function dispatchEvent(string $stateEventName, OrderEntity $order, Context $context): void
    {
        $this->eventDispatcher->dispatch(
            new OrderStateMachineStateChangeEvent($stateEventName, $order, $context),
            $stateEventName
        );
    }
}
