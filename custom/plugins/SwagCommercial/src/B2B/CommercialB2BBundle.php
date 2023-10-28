<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-type Feature array{code: string, name: string, description: string, type: string}
 */
#[Package('checkout')]
abstract class CommercialB2BBundle extends CommercialBundle
{
    final public const TYPE_B2B = 'B2B';

    /**
     * @return list<Feature>
     */
    abstract public function describeFeatures(): array;
}
