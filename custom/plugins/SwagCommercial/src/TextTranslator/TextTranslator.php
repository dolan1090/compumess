<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('inventory')]
class TextTranslator extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'REVIEW_TRANSLATOR',
                'name' => 'Review translator',
                'description' => 'The Review translator is an AI based service that automatically translates product reviews',
            ],
        ];
    }
}
