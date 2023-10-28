<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Order;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\MultiWarehouse\Domain\Storage\StockStorage;
use Shopware\Core\Content\Product\Events\ProductNoLongerAvailableEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Profiling\Profiler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
class MultiWarehouseStockUpdater
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly StockStorage $storage,
        private readonly EntityRepository $orderProductWarehouseRepository,
        private readonly Connection $connection
    ) {
    }

    /**
     * @param array<string, array{quantity: int, closeout: bool}> $products
     */
    public function update(Context $context, string $orderId, array $products): void
    {
        $productIds = \array_keys($products);
        $stocks = $this->storage->load($productIds, $context);

        Profiler::trace(
            'multi-warehouse::update-stock',
            fn () => $this->updateWarehouseStocksForOrder($context, $orderId, $products)
        );

        $this->checkProductAvailability($products, $stocks, $context);
    }

    /**
     * @param array<string, array{quantity: int, closeout: bool}> $products
     * @param array<string, int> $stocks
     */
    private function checkProductAvailability(array $products, array $stocks, Context $context): void
    {
        $unavailable = [];
        foreach ($stocks as $id => $stock) {
            $newStock = $stock - $products[$id]['quantity'];

            if (!$products[$id]['closeout'] || ($newStock > 0)) {
                continue;
            }

            $unavailable[] = $id;
        }

        if (!empty($unavailable)) {
            $this->eventDispatcher->dispatch(new ProductNoLongerAvailableEvent($unavailable, $context));
        }
    }

    /**
     * @param array<string, array{quantity: int, closeout: bool}> $products
     */
    private function updateWarehouseStocksForOrder(Context $context, string $orderId, array $products): void
    {
        $productIds = \array_keys($products);

        $unsortedProductWarehouses = $this->fetchStocksBasedOnWarehouses($productIds, $context);
        $productWarehouses = $this->sortAndFormatProductWarehouseStocks($unsortedProductWarehouses);

        $stocks = $this->calculateOrderStocks($products, $productWarehouses);

        $this->decrease($stocks);
        $this->insertOrderProductWarehouses($orderId, $stocks, $context);
    }

    /**
     * @param array<int, array{warehouseId: string, productId: string, quantity: int}> $stocks
     */
    private function decrease(array $stocks): void
    {
        $sql = <<<'SQL'
            UPDATE product_warehouse SET stock = stock - :quantity
            WHERE product_id = :productId AND warehouse_id = :warehouseId
        SQL;

        $updateQuery = new RetryableQuery($this->connection, $this->connection->prepare($sql));

        foreach ($stocks as $stock) {
            $updateQuery->execute([
                'productId' => Uuid::fromHexToBytes($stock['productId']),
                'warehouseId' => Uuid::fromHexToBytes($stock['warehouseId']),
                'quantity' => $stock['quantity'],
            ]);
        }
    }

    /**
     * @param array<string> $productIds
     *
     * @return array<string, array<array{warehouseId: string, warehouseGroupId: string, stock: int, assigned: bool}>>
     */
    private function fetchStocksBasedOnWarehouses(array $productIds, Context $context): array
    {
        $sql = <<<'SQL'
            SELECT LOWER(HEX(product.id)) AS product_id,
                   LOWER(HEX(product_warehouse_group.warehouse_group_id)) AS warehouse_group_id,
                   LOWER(HEX(product_warehouse.warehouse_id)) AS warehouse_id,
                   COALESCE(product_warehouse.stock, 0) AS stock,
                   warehouse_group.priority AS warehouse_group_priority,
                   warehouse_group_warehouse.priority AS warehouse_priority,
                   (warehouse_group.id IS NOT NULL) AS assigned
            FROM product
            INNER JOIN product_warehouse_group
                ON product.id = product_warehouse_group.product_id
                AND product.version_id = product_warehouse_group.product_version_id
            LEFT JOIN warehouse_group
                ON product_warehouse_group.warehouse_group_id = warehouse_group.id
                AND warehouse_group.rule_id IN (:ruleIds)
            LEFT JOIN warehouse_group_warehouse
                ON warehouse_group_warehouse.warehouse_group_id = product_warehouse_group.warehouse_group_id
            LEFT JOIN product_warehouse
                ON product_warehouse.product_id = product_warehouse_group.product_id
                AND product_warehouse.product_version_id = product_warehouse_group.product_version_id
                AND product_warehouse.warehouse_id = warehouse_group_warehouse.warehouse_id
            WHERE product.id IN (:ids)
            ORDER BY warehouse_group.priority DESC, warehouse_group_warehouse.priority DESC
        SQL;

        /** @var array<int, array<string>> $stocks */
        $stocks = $this->connection->fetchAllAssociative(
            $sql,
            [
                'ids' => Uuid::fromHexToBytesList($productIds),
                'ruleIds' => Uuid::fromHexToBytesList($context->getRuleIds()),
            ],
            [
                'ids' => ArrayParameterType::STRING,
                'ruleIds' => ArrayParameterType::STRING,
            ]
        );

        $mapping = [];
        foreach ($stocks as $stock) {
            if (!$stock['warehouse_group_id']) {
                continue;
            }

            $mapping[$stock['product_id']][] = [
                'warehouseId' => $stock['warehouse_id'],
                'warehouseGroupId' => $stock['warehouse_group_id'],
                'stock' => (int) $stock['stock'],
                'assigned' => (bool) $stock['assigned'],
            ];
        }

        return $mapping;
    }

    /**
     * @param array<int, array{warehouseId: string, productId: string, quantity: int}> $stocks
     */
    private function insertOrderProductWarehouses(string $orderId, array $stocks, Context $context): void
    {
        $data = [];
        foreach ($stocks as $stock) {
            $data[] = [
                'versionId' => $context->getVersionId(),
                'orderId' => $orderId,
                'orderVersionId' => $context->getVersionId(),
                'productId' => $stock['productId'],
                'productVersionId' => $context->getVersionId(),
                'warehouseId' => $stock['warehouseId'],
                'quantity' => $stock['quantity'],
            ];
        }

        $this->orderProductWarehouseRepository->create($data, $context);
    }

    /**
     * @param array<string, array{quantity: int, closeout: bool}> $products
     * @param array<string, array<string, int>> $productWarehouses
     *
     * @return array<int, array{warehouseId: string, productId: string, quantity: int}>
     */
    private function calculateOrderStocks(array $products, array $productWarehouses): array
    {
        $updateStocks = [];

        foreach ($products as $productId => $product) {
            /** @var array<string, int> $warehouses */
            $warehouses = $productWarehouses[$productId] ?? null;
            if (empty($warehouses)) {
                throw new \LogicException(sprintf('Product "%s" has no warehouses', $productId));
            }

            $emptyWarehouses = \array_filter($warehouses, static fn (int $stock) => $stock <= 0);

            // if all warehouses are empty, use warehouse with the highest priority
            if (\count($emptyWarehouses) === \count($warehouses)) {
                $warehouseId = \array_key_first($warehouses);

                $updateStocks[] = [
                    'warehouseId' => $warehouseId,
                    'productId' => $productId,
                    'quantity' => $product['quantity'],
                ];

                continue;
            }

            $warehouses = \array_diff_key($warehouses, $emptyWarehouses);
            $lastWarehouseId = \array_key_last($warehouses);

            $leftQty = $product['quantity'];
            foreach ($warehouses as $warehouseId => $stock) {
                $decreasedQty = $leftQty - $stock;

                if ($decreasedQty < 0) {
                    $updateStocks[] = [
                        'warehouseId' => $warehouseId,
                        'productId' => $productId,
                        'quantity' => $leftQty,
                    ];

                    $productWarehouses[$productId][$warehouseId] -= $leftQty;
                    $leftQty -= $leftQty;

                    continue;
                }

                // if not enough stock in warehouses, decrease remaining from last
                if ($warehouseId === $lastWarehouseId) {
                    $updateStocks[] = [
                        'warehouseId' => $warehouseId,
                        'productId' => $productId,
                        'quantity' => $leftQty,
                    ];

                    $productWarehouses[$productId][$warehouseId] -= $leftQty;

                    break;
                }

                $updateStocks[] = [
                    'warehouseId' => $warehouseId,
                    'productId' => $productId,
                    'quantity' => $stock,
                ];

                $productWarehouses[$productId][$warehouseId] -= $stock;
                $leftQty -= $stock;
            }
        }

        return $updateStocks;
    }

    /**
     * @param array<string, array<array{warehouseId: string, warehouseGroupId: string, stock: int, assigned: bool}>> $productWarehouses
     *
     * @return array<string, array<string, int>>
     */
    private function sortAndFormatProductWarehouseStocks(array $productWarehouses): array
    {
        $formatted = [];
        foreach ($productWarehouses as $id => $warehouses) {
            // select assigned and valid group with first found stocks
            $selectedGroup = null;
            foreach ($warehouses as $warehouse) {
                if ($warehouse['stock'] <= 0 || !$warehouse['assigned']) {
                    continue;
                }

                $selectedGroup = $warehouse['warehouseGroupId'];

                break;
            }

            // if no group with stocks was found, use first group
            if (!$selectedGroup && !empty($warehouses)) {
                $selectedGroup = current($warehouses)['warehouseGroupId'];
            }

            // collect all warehouses from selected group
            $warehouses = \array_filter($warehouses, static fn (array $warehouse) => $warehouse['warehouseGroupId'] === $selectedGroup);

            $warehouseStocks = [];
            foreach ($warehouses as $warehouse) {
                $warehouseStocks[$warehouse['warehouseId']] = $warehouse['stock'];
            }

            $formatted[$id] = $warehouseStocks;
        }

        return $formatted;
    }
}
