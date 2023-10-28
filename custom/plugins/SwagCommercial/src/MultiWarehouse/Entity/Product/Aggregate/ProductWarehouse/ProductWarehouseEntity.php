<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductWarehouseEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;

    protected string $productVersionId;

    protected string $warehouseId;

    protected int $stock;

    protected ?ProductEntity $product = null;

    protected ?WarehouseEntity $warehouse = null;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getWarehouse(): ?WarehouseEntity
    {
        return $this->warehouse;
    }

    public function setWarehouse(?WarehouseEntity $warehouse): void
    {
        $this->warehouse = $warehouse;
    }
}
