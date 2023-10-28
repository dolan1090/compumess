<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class OrderProductWarehouseEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderId;

    protected string $orderVersionId;

    protected string $warehouseId;

    protected string $productId;

    protected int $quantity;

    protected ?OrderEntity $order = null;

    protected ?ProductEntity $product = null;

    protected ?WarehouseCollection $warehouses = null;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderVersionId(): string
    {
        return $this->orderVersionId;
    }

    public function setOrderVersionId(string $orderVersionId): void
    {
        $this->orderVersionId = $orderVersionId;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getWarehouses(): ?WarehouseCollection
    {
        return $this->warehouses;
    }

    public function setWarehouses(WarehouseCollection $warehouses): void
    {
        $this->warehouses = $warehouses;
    }

    public function getApiAlias(): string
    {
        return 'order_product_warehouse';
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }
}
