<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('inventory')]
class ReviewSummary extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'REVIEW_SUMMARY',
                'name' => 'Review summary',
                'description' => 'The review summary feature creates an AI based abstract of a products reviews and points out what customers think about the product.',
            ],
        ];
    }
}
