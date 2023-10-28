<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('checkout')]
class CheckoutSweetener extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'CHECKOUT_SWEETENER',
                'name' => 'Personalized checkout message',
                'description' => 'This feature allows you to display a AI generated personalized message during the checkout process, which can help sweeten the overall shopping experience for your customers.',
            ],
        ];
    }
}
