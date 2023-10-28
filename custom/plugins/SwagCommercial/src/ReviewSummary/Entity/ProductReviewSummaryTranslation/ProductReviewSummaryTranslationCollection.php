<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummaryTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductReviewSummaryTranslationEntity>
 */
#[Package('inventory')]
class ProductReviewSummaryTranslationCollection extends EntityCollection
{
    /**
     * @return array<mixed, mixed>
     */
    public function getLanguageIds(): array
    {
        return $this->fmap(fn (ProductReviewSummaryTranslationEntity $productManufacturerTranslation) => $productManufacturerTranslation->getLanguageId());
    }

    public function filterByLanguageId(string $id): self
    {
        return $this->filter(fn (ProductReviewSummaryTranslationEntity $productManufacturerTranslation) => $productManufacturerTranslation->getLanguageId() === $id);
    }

    protected function getExpectedClass(): string
    {
        return ProductReviewSummaryTranslationEntity::class;
    }
}
