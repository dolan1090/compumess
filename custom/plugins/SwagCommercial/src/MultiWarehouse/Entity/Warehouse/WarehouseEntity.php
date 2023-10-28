<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Warehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse\OrderProductWarehouseCollection;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse\ProductWarehouseCollection;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected string $description;

    protected ?ProductWarehouseCollection $productWarehouses = null;

    protected ?WarehouseGroupCollection $groups = null;

    protected ?OrderProductWarehouseCollection $orderProducts = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getProductWarehouses(): ?ProductWarehouseCollection
    {
        return $this->productWarehouses;
    }

    public function setProductWarehouses(ProductWarehouseCollection $productWarehouses): void
    {
        $this->productWarehouses = $productWarehouses;
    }

    public function getGroups(): ?WarehouseGroupCollection
    {
        return $this->groups;
    }

    public function setGroups(WarehouseGroupCollection $groups): void
    {
        $this->groups = $groups;
    }

    public function getOrderProducts(): ?OrderProductWarehouseCollection
    {
        return $this->orderProducts;
    }

    public function setOrderProducts(OrderProductWarehouseCollection $orderProducts): void
    {
        $this->orderProducts = $orderProducts;
    }
}
