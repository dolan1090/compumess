<?php declare(strict_types=1);

namespace Shopware\Commercial\TextGenerator;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('inventory')]
class TextGenerator extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'TEXT_GENERATOR',
                'name' => 'Description assistant',
                'description' => 'The Description assistant is an AI based service that automatically generates a product description as a source of inspiration',
            ],
        ];
    }
}
