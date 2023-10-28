<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\B2B\QuickOrder;

use Shopware\Commercial\B2B\CommercialB2BBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialB2BBundle
 */
#[Package('checkout')]
class QuickOrder extends CommercialB2BBundle
{
    public const CODE = 'QUICK_ORDER';

    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => self::CODE,
                'name' => 'Quick order',
                'description' => 'Your customers can speed up their ordering process by entering product numbers or uploading files.',
                'type' => self::TYPE_B2B,
            ],
        ];
    }
}
