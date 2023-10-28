<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummaryTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductReviewSummaryTranslationEntity extends TranslationEntity
{
    protected string $summary;

    protected bool $visible;

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }
}
