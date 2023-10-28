<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('inventory')]
class MultiWarehouse extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'MULTI_INVENTORY',
                'name' => 'Multi Inventory',
                'description' => 'Multi Inventory',
            ],
        ];
    }
}
