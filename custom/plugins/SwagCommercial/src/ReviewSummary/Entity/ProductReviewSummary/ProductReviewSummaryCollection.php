<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummary;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<ProductReviewSummaryEntity>
 */
#[Package('inventory')]
class ProductReviewSummaryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductReviewSummaryEntity::class;
    }
}
