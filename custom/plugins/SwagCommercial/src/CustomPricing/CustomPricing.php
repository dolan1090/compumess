<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('inventory')]
class CustomPricing extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'CUSTOM_PRICES',
                'name' => 'Customer specific prices',
                'description' => 'Customer specific prices',
            ],
        ];
    }
}
