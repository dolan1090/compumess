<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Storage\StockStorage;
use Shopware\Core\Content\Product\AbstractProductMaxPurchaseCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductLoadedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly StockStorage $storage,
        private readonly AbstractProductMaxPurchaseCalculator $maxPurchaseCalculator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.loaded' => 'onProductLoaded',
        ];
    }

    public function onProductLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return;
        }

        $products = $this->getCloseoutProducts($event);

        if (!$products) {
            return;
        }

        $this->load(
            $products,
            $event,
        );
    }

    /**
     * @param SalesChannelProductEntity[] $products
     */
    private function load(array $products, SalesChannelEntityLoadedEvent $event): void
    {
        $ids = array_column($products, 'id');
        $context = $event->getSalesChannelContext();

        $stocks = $this->storage->load($ids, $context->getContext());

        if (!$stocks) {
            return;
        }

        foreach ($stocks as $productId => $stock) {
            /** @var SalesChannelProductEntity[] $entities */
            $entities = $event->getEntities();
            $product = $this->getProductById($productId, $entities);

            if ($stock <= 0) {
                $this->blockProduct($product);

                continue;
            }

            $this->updateProduct($product, $stock, $context);
        }
    }

    private function updateProduct(
        SalesChannelProductEntity $product,
        int $stock,
        SalesChannelContext $context
    ): void {
        $product->setStock($stock);
        $product->setAvailableStock($stock);
        $product->setCalculatedMaxPurchase($this->maxPurchaseCalculator->calculate($product, $context));

        $available = $stock >= (int) $product->getMinPurchase();
        $product->setAvailable($available);
    }

    /**
     * @return SalesChannelProductEntity[]
     */
    private function getCloseoutProducts(SalesChannelEntityLoadedEvent $event): array
    {
        /** @var SalesChannelProductEntity[] $products */
        $products = $event->getEntities();

        return \array_filter($products, static fn (SalesChannelProductEntity $product) => $product->getIsCloseout());
    }

    /**
     * @param SalesChannelProductEntity[] $products
     */
    private function getProductById(string $id, array $products): SalesChannelProductEntity
    {
        foreach ($products as $product) {
            if ($product->getId() !== $id) {
                continue;
            }

            return $product;
        }

        throw new \LogicException('Product with given id not found');
    }

    private function blockProduct(SalesChannelProductEntity $product): void
    {
        $product->setAvailableStock(0);
        $product->setAvailable(false);
        $product->setCalculatedMaxPurchase(0);
    }
}
