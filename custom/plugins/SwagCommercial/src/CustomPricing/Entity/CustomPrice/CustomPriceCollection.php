<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\CustomPrice;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomPriceEntity>
 */
#[Package('inventory')]
class CustomPriceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CustomPriceEntity::class;
    }
}
