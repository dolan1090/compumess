<?php declare(strict_types=1);

namespace Shopware\Commercial;

use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-type Feature array{code: string, name: string, description: string, type?: string}
 */
#[Package('core')]
abstract class CommercialBundle extends Bundle
{
    /**
     * @return list<Feature>
     */
    abstract public function describeFeatures(): array;

    /**
     * @return array<string, array<string>>
     */
    public function enrichPrivileges(): array
    {
        return [];
    }
}
