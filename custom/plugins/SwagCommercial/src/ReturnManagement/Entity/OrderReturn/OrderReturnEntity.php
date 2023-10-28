<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturn;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\User\UserEntity;

#[Package('checkout')]
class OrderReturnEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderId;

    protected ?OrderEntity $order = null;

    protected string $stateId;

    protected ?StateMachineStateEntity $state = null;

    protected string $returnNumber;

    protected \DateTimeInterface $requestedAt;

    protected ?string $internalComment = null;

    protected ?CartPrice $price = null;

    protected ?CalculatedPrice $shippingCosts = null;

    protected ?float $amountTotal = null;

    protected ?float $amountNet = null;

    protected ?string $createdById = null;

    protected ?UserEntity $createdBy = null;

    protected ?string $updatedById = null;

    protected ?UserEntity $updatedBy = null;

    protected ?OrderReturnLineItemCollection $lineItems = null;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getReturnNumber(): string
    {
        return $this->returnNumber;
    }

    public function setReturnNumber(string $returnNumber): void
    {
        $this->returnNumber = $returnNumber;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): void
    {
        $this->internalComment = $internalComment;
    }

    public function getLineItems(): ?OrderReturnLineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(OrderReturnLineItemCollection $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    public function getState(): ?StateMachineStateEntity
    {
        return $this->state;
    }

    public function setState(StateMachineStateEntity $state): void
    {
        $this->state = $state;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): void
    {
        $this->requestedAt = $requestedAt;
    }

    public function getCreatedById(): ?string
    {
        return $this->createdById;
    }

    public function setCreatedById(?string $createdById): void
    {
        $this->createdById = $createdById;
    }

    public function getCreatedBy(): ?UserEntity
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserEntity $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedById(): ?string
    {
        return $this->updatedById;
    }

    public function setUpdatedById(?string $updatedById): void
    {
        $this->updatedById = $updatedById;
    }

    public function getUpdatedBy(): ?UserEntity
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?UserEntity $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getPrice(): ?CartPrice
    {
        return $this->price;
    }

    public function setPrice(?CartPrice $price): void
    {
        $this->price = $price;
    }

    public function getAmountTotal(): ?float
    {
        return $this->amountTotal;
    }

    public function setAmountTotal(?float $amountTotal): void
    {
        $this->amountTotal = $amountTotal;
    }

    public function getAmountNet(): ?float
    {
        return $this->amountNet;
    }

    public function setAmountNet(?float $amountNet): void
    {
        $this->amountNet = $amountNet;
    }

    public function getShippingCosts(): ?CalculatedPrice
    {
        return $this->shippingCosts;
    }

    public function setShippingCosts(?CalculatedPrice $shippingCosts): void
    {
        $this->shippingCosts = $shippingCosts;
    }
}
