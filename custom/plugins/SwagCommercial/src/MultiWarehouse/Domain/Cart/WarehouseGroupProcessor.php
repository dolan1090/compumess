<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Cart;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Storage\StockStorage;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('inventory')]
class WarehouseGroupProcessor implements CartDataCollectorInterface
{
    public function __construct(
        private readonly StockStorage $storage
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return;
        }

        $items = $original->getLineItems()
            ->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        if (!$items) {
            return;
        }

        $this->load($items, $context);
    }

    /**
     * @param LineItem[] $elements
     */
    private function load(array $elements, SalesChannelContext $context): void
    {
        $ids = [];
        foreach ($elements as $item) {
            if (!$item->getPayloadValue('isCloseout')) {
                continue;
            }

            $ids[] = (string) $item->getReferencedId();
        }

        if (!$ids) {
            return;
        }

        $stocks = $this->storage->load($ids, $context->getContext());

        if (!$stocks) {
            return;
        }

        foreach ($stocks as $productId => $stock) {
            $item = $this->getLineItemById($productId, $elements);

            if ($stock <= 0) {
                $this->blockLineItem($item);

                continue;
            }

            $this->updateLineItem($item, $stock);
        }
    }

    private function updateLineItem(
        LineItem $item,
        int $stock
    ): void {
        $item->setPayloadValue('stock', $stock);

        $quantity = new QuantityInformation();
        $oldQuantity = $item->getQuantityInformation();

        if (!$oldQuantity) {
            $quantity->setMaxPurchase($stock);
            $item->setQuantityInformation($quantity);

            return;
        }

        $maxPurchase = $oldQuantity->getMaxPurchase() ?? $stock;

        if ($stock < $maxPurchase) {
            $maxPurchase = $stock;
        }

        $quantity->setMaxPurchase($maxPurchase);
        $quantity->setMinPurchase($oldQuantity->getMinPurchase());
        $quantity->setPurchaseSteps($oldQuantity->getPurchaseSteps() ?? 1);

        $item->setQuantityInformation($quantity);
    }

    private function blockLineItem(LineItem $item): void
    {
        $quantity = new QuantityInformation();
        $quantity->setMaxPurchase(0);

        $item->setQuantityInformation($quantity);
    }

    /**
     * @param LineItem[] $items
     */
    private function getLineItemById(string $id, array $items): LineItem
    {
        foreach ($items as $item) {
            if ($item->getReferencedId() !== $id) {
                continue;
            }

            return $item;
        }

        throw new \LogicException('Line item with given reference id not found');
    }
}
