<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle as FeatureSpec
 */
#[Package('merchant-services')]
class FeaturesFactory
{
    /**
     * @param list<FeatureSpec> $featureSpecs
     */
    public function __invoke(SystemConfigService $config, array $featureSpecs): Features
    {
        return new Features(
            $config,
            array_map(
                fn (array $feature) => new Feature(
                    $feature['code'],
                    $feature['name'],
                    $feature['description'],
                    $feature['type'] ?? null
                ),
                $featureSpecs
            )
        );
    }
}
