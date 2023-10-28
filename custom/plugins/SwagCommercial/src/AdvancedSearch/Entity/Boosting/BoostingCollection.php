<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\Boosting;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<BoostingEntity>
 */
#[Package('buyers-experience')]
class BoostingCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BoostingEntity::class;
    }
}
