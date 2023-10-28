<?php declare(strict_types=1);

namespace Shopware\Commercial\ContentGenerator;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('content')]
class ContentGenerator extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'CONTENT_GENERATOR',
                'name' => 'Shopping experience assistant',
                'description' => 'The content assistant is an AI-based service that automatically generates content as a source of inspiration',
            ],
        ];
    }
}
