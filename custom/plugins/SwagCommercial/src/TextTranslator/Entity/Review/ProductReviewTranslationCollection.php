<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Entity\Review;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductReviewTranslationEntity>
 */
#[Package('inventory')]
class ProductReviewTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_review_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductReviewTranslationEntity::class;
    }
}
