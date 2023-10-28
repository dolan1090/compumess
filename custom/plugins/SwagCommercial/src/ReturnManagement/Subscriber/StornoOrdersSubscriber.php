<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnCollection;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Document\Event\StornoOrdersEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class StornoOrdersSubscriber implements EventSubscriberInterface
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
            StornoOrdersEvent::class => 'adjustLineItems',
        ];
    }

    public function adjustLineItems(StornoOrdersEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $orders = $event->getOrders();

        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            /** @var OrderReturnCollection|null $returns */
            $returns = $order->getExtension('returns');
            if ($returns === null || \count($returns) === 0) {
                continue;
            }

            if ($order->getLineItems() === null || $order->getVersionId() === null) {
                continue;
            }

            //  Full cancellation of the invoice should only show the items that have been used for the invoice selected for the cancellation
            $criteria = OrderLineItemStatesCriteriaFactory::createNotInStates(
                $order->getLineItems()->getIds(),
                [PositionStateHandler::STATE_CANCELLED, PositionStateHandler::STATE_RETURNED]
            );

            $versionContext = $event->getContext()->createWithVersionId($order->getVersionId());

            // TODO: We need to refactor the search in a loop when we have a solution for searching each version context of the order
            /** @var OrderLineItemCollection $orderLineItems */
            $orderLineItems = $this->orderLineItemRepository->search($criteria, $versionContext)->getEntities();
            $salesChannelContext = $this->contextRestorer->restoreByOrder($order->getId(), $event->getContext());
            $lineItems = $this->documentReturnCalculator->calculate($orderLineItems, $salesChannelContext);

            $orderPrice = $this->amountCalculator->calculate($lineItems->getPrices(), new PriceCollection(), $salesChannelContext);
            $order->setLineItems($lineItems);
            $order->setPrice($orderPrice);
            $order->setAmountNet($orderPrice->getNetPrice());
        }
    }
}
