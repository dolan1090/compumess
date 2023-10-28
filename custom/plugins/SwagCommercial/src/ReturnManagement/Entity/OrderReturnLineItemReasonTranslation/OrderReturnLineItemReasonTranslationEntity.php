<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderReturnLineItemReasonTranslationEntity extends TranslationEntity
{
    use EntityIdTrait;

    protected ?string $content = null;

    protected string $orderReturnLineItemReasonId;

    protected ?OrderReturnLineItemReasonEntity $orderReturnLineItemReason = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getOrderReturnLineItemReasonId(): string
    {
        return $this->orderReturnLineItemReasonId;
    }

    public function setOrderReturnLineItemReasonId(string $orderReturnLineItemReasonId): void
    {
        $this->orderReturnLineItemReasonId = $orderReturnLineItemReasonId;
    }

    public function getOrderReturnLineItemReason(): ?OrderReturnLineItemReasonEntity
    {
        return $this->orderReturnLineItemReason;
    }

    public function setOrderReturnLineItemReason(?OrderReturnLineItemReasonEntity $orderReturnLineItemReason): void
    {
        $this->orderReturnLineItemReason = $orderReturnLineItemReason;
    }
}
