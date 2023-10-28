<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductWarehouseGroupEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;

    protected string $productVersionId;

    protected string $warehouseGroupId;

    protected ?ProductEntity $product = null;

    protected ?WarehouseGroupEntity $warehouseGroup = null;

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

    public function getWarehouseGroupId(): string
    {
        return $this->warehouseGroupId;
    }

    public function setWarehouseGroupId(string $warehouseGroupId): void
    {
        $this->warehouseGroupId = $warehouseGroupId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getWarehouseGroup(): ?WarehouseGroupEntity
    {
        return $this->warehouseGroup;
    }

    public function setWarehouseGroup(?WarehouseGroupEntity $warehouseGroup): void
    {
        $this->warehouseGroup = $warehouseGroup;
    }
}
