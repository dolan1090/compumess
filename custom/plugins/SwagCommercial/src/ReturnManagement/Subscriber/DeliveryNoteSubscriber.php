<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnCollection;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Document\Event\DeliveryNoteOrdersEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class DeliveryNoteSubscriber implements EventSubscriberInterface
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $orderLineItemRepository,
        private readonly DocumentReturnCalculator $documentReturnCalculator,
        private readonly SalesChannelContextRestorer $contextRestorer,
        private readonly AmountCalculator $amountCalculator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DeliveryNoteOrdersEvent::class => 'adjustLineItems',
        ];
    }

    public function adjustLineItems(DeliveryNoteOrdersEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $orders = $event->getOrders();

        $lineItemsIds = [];
        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            /** @var OrderReturnCollection|null $returns */
            $returns = $order->getExtension('returns');
            if ($returns === null || \count($returns) === 0) {
                continue;
            }

            $lineItemsIds = array_merge($lineItemsIds, $order->getLineItems() ? $order->getLineItems()->getIds() : []);
        }

        if (empty($lineItemsIds)) {
            return;
        }

        $criteria = OrderLineItemStatesCriteriaFactory::createNotInStates(
            $lineItemsIds,
            [PositionStateHandler::STATE_CANCELLED, PositionStateHandler::STATE_RETURNED]
        );

        /** @var OrderLineItemCollection $lineItems */
        $lineItems = $this->orderLineItemRepository->search($criteria, $event->getContext())->getEntities();

        $orderMappedLineItems = [];
        /** @var OrderLineItemEntity $lineItem */
        foreach ($lineItems as $lineItem) {
            $orderMappedLineItems[$lineItem->getOrderId()] ??= new OrderLineItemCollection();
            $orderMappedLineItems[$lineItem->getOrderId()]->add($lineItem);
        }

        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            $lineItems = $orderMappedLineItems[$order->getId()] ?? new OrderLineItemCollection();

            $salesChannelContext = $this->contextRestorer->restoreByOrder($order->getId(), $event->getContext());
            $lineItems = $this->documentReturnCalculator->calculate($lineItems, $salesChannelContext);

            $orderPrice = $this->amountCalculator->calculate(new PriceCollection(), new PriceCollection(), $salesChannelContext);

            $order->setLineItems($lineItems);
            $order->setPrice($orderPrice);
            $order->setAmountNet($orderPrice->getNetPrice());
        }
    }
}
