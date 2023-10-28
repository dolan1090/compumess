<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderWarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class OrderWarehouseGroupEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderId;

    protected string $warehouseGroupId;

    protected ?OrderEntity $order = null;

    protected ?WarehouseGroupEntity $warehouseGroup = null;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getWarehouseGroupId(): string
    {
        return $this->warehouseGroupId;
    }

    public function setWarehouseGroupId(string $warehouseGroupId): void
    {
        $this->warehouseGroupId = $warehouseGroupId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
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
