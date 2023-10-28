<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\ClassificationCustomer;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('checkout')]
class ClassificationCustomer extends CommercialBundle
{
    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'CLASSIFICATION_CUSTOMER',
                'name' => 'Classification customer',
                'description' => 'Classification customer',
            ],
        ];
    }
}
