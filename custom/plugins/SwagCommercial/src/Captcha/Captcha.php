<?php declare(strict_types=1);

namespace Shopware\Commercial\Captcha;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('checkout')]
class Captcha extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'CAPTCHA',
                'name' => 'Captcha',
                'description' => 'Captcha',
            ],
        ];
    }
}
