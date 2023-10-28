<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemCollection;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation\OrderReturnLineItemReasonTranslationCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderReturnLineItemReasonEntity extends Entity
{
    use EntityIdTrait;

    protected string $reasonKey;

    protected ?string $content = null;

    protected ?OrderReturnLineItemCollection $lineItems = null;

    protected ?OrderReturnLineItemReasonTranslationCollection $translations = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getTranslations(): ?OrderReturnLineItemReasonTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(OrderReturnLineItemReasonTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getLineItems(): ?OrderReturnLineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(OrderReturnLineItemCollection $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    public function getReasonKey(): string
    {
        return $this->reasonKey;
    }

    public function setReasonKey(string $reasonKey): void
    {
        $this->reasonKey = $reasonKey;
    }
}
