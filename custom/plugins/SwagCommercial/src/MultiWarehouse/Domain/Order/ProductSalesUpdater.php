<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Order;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
class ProductSalesUpdater
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function increaseSales(string $orderId, Context $context): void
    {
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        $quantities = $this->getOrderQuantities($orderId, $context);

        $this->updateSales($quantities, +1);
    }

    public function decreaseSales(string $orderId, Context $context): void
    {
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        $quantities = $this->getOrderQuantities($orderId, $context);

        $this->updateSales($quantities, -1);
    }

    /**
     * @param array<string, int> $products
     */
    private function updateSales(array $products, int $multiplier): void
    {
        $query = new RetryableQuery($this->connection, $this->connection->prepare(
            'UPDATE product SET sales = sales + :sales WHERE id = :id'
        ));

        foreach ($products as $id => $qty) {
            $query->execute([
                'id' => Uuid::fromHexToBytes($id),
                'sales' => $qty * $multiplier,
            ]);
        }
    }

    /**
     * @return array<string, int>
     */
    private function getOrderQuantities(string $orderId, Context $context): array
    {
        $sql = <<<'SQL'
            SELECT order_line_item.referenced_id, order_line_item.quantity AS quantity
            FROM order_line_item
                INNER JOIN product_warehouse ON product_warehouse.product_id = order_line_item.product_id
                    AND product_warehouse.product_version_id = order_line_item.product_version_id
            WHERE order_line_item.order_id = :orderId AND order_line_item.order_version_id = :versionId
                AND order_line_item.type = :type
            GROUP BY order_line_item.referenced_id
        SQL;

        /** @var array<string, int> $quantities */
        $quantities = $this->connection->fetchAllKeyValue(
            $sql,
            [
                'orderId' => Uuid::fromHexToBytes($orderId),
                'versionId' => Uuid::fromHexToBytes($context->getVersionId()),
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            ]
        );

        return $quantities;
    }
}
