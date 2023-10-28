<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('administration')]
class ImageClassification extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'IMAGE_CLASSIFICATION',
                'name' => 'Image classification',
                'description' => 'The image classification is an AI based service that automatically generates image tags',
            ],
        ];
    }
}
