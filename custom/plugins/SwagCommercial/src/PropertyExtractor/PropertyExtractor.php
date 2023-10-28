<?php declare(strict_types=1);

namespace Shopware\Commercial\PropertyExtractor;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('business-ops')]
class PropertyExtractor extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'PROPERTY_EXTRACTOR',
                'name' => 'Property assistant',
                'description' => 'The property assistant is an AI based service that automatically extracts properties from a text',
            ],
        ];
    }
}
