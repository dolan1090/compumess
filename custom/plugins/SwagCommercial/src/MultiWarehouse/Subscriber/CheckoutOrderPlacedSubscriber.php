<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Order\MultiWarehouseStockUpdater;
use Shopware\Commercial\MultiWarehouse\Domain\Product\MultiWarehouseProductFilter;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class CheckoutOrderPlacedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MultiWarehouseStockUpdater $stockUpdater,
        private readonly MultiWarehouseProductFilter $productFilter
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return;
        }

        $lineItems = $event->getOrder()->getLineItems();

        if ($lineItems === null || $lineItems->count() <= 0) {
            return;
        }

        /** @var list<string> $lineItemRefIds */
        $lineItemRefIds = $lineItems->fmap(static function (OrderLineItemEntity $orderLineItem) {
            if ($orderLineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                return null;
            }

            return $orderLineItem->getReferencedId();
        });

        $warehouseLineItemIds = $this->productFilter
            ->filterProductIdsWithWarehouses($lineItemRefIds, $event->getContext());

        if (empty($warehouseLineItemIds)) {
            return;
        }

        $data = [];

        /** @var OrderLineItemEntity $lineItem */
        foreach ($lineItems as $lineItem) {
            if (!\in_array($lineItem->getReferencedId(), $warehouseLineItemIds, true)) {
                continue;
            }

            if (!\array_key_exists($lineItem->getReferencedId(), $data)) {
                $data[$lineItem->getReferencedId()]['quantity'] = 0;
            }

            $data[$lineItem->getReferencedId()]['quantity'] += $lineItem->getQuantity();

            /** @var array<string, mixed> $payload */
            $payload = $lineItem->getPayload();
            $data[$lineItem->getReferencedId()]['closeout'] = (bool) $payload['isCloseout'];
        }

        $this->stockUpdater->update(
            $event->getContext(),
            $event->getOrderId(),
            $data
        );
    }
}
