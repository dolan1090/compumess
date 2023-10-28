<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Entity;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceEntity;
use Shopware\Core\Content\Flow\FlowEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('business-ops')]
class DelayActionEntity extends Entity
{
    use EntityIdTrait;

    protected string $eventName;

    protected string $flowId;

    protected ?FlowEntity $flow = null;

    protected ?string $orderId = null;

    protected ?OrderEntity $order = null;

    protected ?string $orderVersionId = null;

    protected ?string $customerId = null;

    protected ?CustomerEntity $customer = null;

    protected string $delaySequenceId;

    protected ?\DateTimeInterface $executionTime = null;

    protected bool $expired;

    protected ?FlowSequenceEntity $sequence = null;

    /**
     * @var array<string, mixed>
     */
    protected $stored;

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getFlowId(): string
    {
        return $this->flowId;
    }

    public function setFlowId(string $flowId): void
    {
        $this->flowId = $flowId;
    }

    public function getFlow(): ?FlowEntity
    {
        return $this->flow;
    }

    public function setFlow(?FlowEntity $flow): void
    {
        $this->flow = $flow;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderVersionId(): ?string
    {
        return $this->orderVersionId;
    }

    public function setOrderVersionId(?string $orderVersionId): void
    {
        $this->orderVersionId = $orderVersionId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }

    public function getExecutionTime(): ?\DateTimeInterface
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?\DateTimeInterface $executionTime): void
    {
        $this->executionTime = $executionTime;
    }

    public function getDelaySequenceId(): string
    {
        return $this->delaySequenceId;
    }

    public function getSequence(): ?FlowSequenceEntity
    {
        return $this->sequence;
    }

    public function setSequence(?FlowSequenceEntity $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function setDelaySequenceId(string $delaySequenceId): void
    {
        $this->delaySequenceId = $delaySequenceId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStored(): array
    {
        return $this->stored;
    }

    /**
     * @param array<string, mixed> $stored
     */
    public function setStored(array $stored): void
    {
        $this->stored = $stored;
    }
}
