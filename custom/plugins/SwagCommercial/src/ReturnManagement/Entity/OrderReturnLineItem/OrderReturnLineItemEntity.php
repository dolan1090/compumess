<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonEntity;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

#[Package('checkout')]
class OrderReturnLineItemEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $orderReturnId;

    protected ?OrderReturnEntity $return = null;

    protected string $orderLineItemId;

    protected ?OrderLineItemEntity $lineItem = null;

    protected ?string $reasonId = null;

    protected ?OrderReturnLineItemReasonEntity $reason = null;

    protected int $quantity;

    protected ?string $internalComment = null;

    protected ?CalculatedPrice $price = null;

    protected float $refundAmount;

    protected int $restockQuantity;

    protected string $stateId;

    protected ?StateMachineStateEntity $state = null;

    public function getOrderReturnId(): string
    {
        return $this->orderReturnId;
    }

    public function setOrderReturnId(string $orderReturnId): void
    {
        $this->orderReturnId = $orderReturnId;
    }

    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }

    public function setOrderLineItemId(string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): void
    {
        $this->internalComment = $internalComment;
    }

    public function getReturn(): ?OrderReturnEntity
    {
        return $this->return;
    }

    public function setReturn(OrderReturnEntity $return): void
    {
        $this->return = $return;
    }

    public function getLineItem(): ?OrderLineItemEntity
    {
        return $this->lineItem;
    }

    public function setLineItem(?OrderLineItemEntity $lineItem): void
    {
        $this->lineItem = $lineItem;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    public function getState(): ?StateMachineStateEntity
    {
        return $this->state;
    }

    public function setState(?StateMachineStateEntity $state): void
    {
        $this->state = $state;
    }

    public function getReasonId(): ?string
    {
        return $this->reasonId;
    }

    public function setReasonId(?string $reasonId): void
    {
        $this->reasonId = $reasonId;
    }

    public function getReason(): ?OrderReturnLineItemReasonEntity
    {
        return $this->reason;
    }

    public function setReason(OrderReturnLineItemReasonEntity $reason): void
    {
        $this->reason = $reason;
    }

    public function getPrice(): ?CalculatedPrice
    {
        return $this->price;
    }

    public function setPrice(?CalculatedPrice $price): void
    {
        $this->price = $price;
    }

    public function getRestockQuantity(): int
    {
        return $this->restockQuantity;
    }

    public function setRestockQuantity(int $restockQuantity): void
    {
        $this->restockQuantity = $restockQuantity;
    }

    public function getRefundAmount(): float
    {
        return $this->refundAmount;
    }

    public function setRefundAmount(float $refundAmount): void
    {
        $this->refundAmount = $refundAmount;
    }
}
