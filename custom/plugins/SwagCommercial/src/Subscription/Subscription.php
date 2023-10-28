<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('checkout')]
class Subscription extends CommercialBundle
{
    /**
     * @var string
     */
    protected $name = 'Subscription';

    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'SUBSCRIPTIONS',
                'name' => 'Subscriptions',
                'description' => 'Subscriptions',
            ],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function enrichPrivileges(): array
    {
        return [
            'product.viewer' => [
                'subscription_plan:read',
            ],
        ];
    }
}
