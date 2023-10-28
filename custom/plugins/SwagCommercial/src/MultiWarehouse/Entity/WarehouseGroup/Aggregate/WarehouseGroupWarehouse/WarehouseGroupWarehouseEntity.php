<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseCollection;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseGroupWarehouseEntity extends Entity
{
    use EntityIdTrait;

    protected string $warehouseId;

    protected string $warehouseGroupId;

    protected int $priority = 1;

    protected ?WarehouseGroupCollection $warehouseGroups = null;

    protected ?WarehouseCollection $warehouses = null;

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    public function getWarehouseGroupId(): string
    {
        return $this->warehouseGroupId;
    }

    public function setWarehouseGroupId(string $warehouseGroupId): void
    {
        $this->warehouseGroupId = $warehouseGroupId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority = 1): void
    {
        $this->priority = $priority;
    }

    public function getWarehouseGroups(): ?WarehouseGroupCollection
    {
        return $this->warehouseGroups;
    }

    public function setWarehouseGroups(?WarehouseGroupCollection $warehouseGroups): void
    {
        $this->warehouseGroups = $warehouseGroups;
    }

    public function getWarehouses(): ?WarehouseCollection
    {
        return $this->warehouses;
    }

    public function setWarehouses(?WarehouseCollection $warehouses): void
    {
        $this->warehouses = $warehouses;
    }
}
