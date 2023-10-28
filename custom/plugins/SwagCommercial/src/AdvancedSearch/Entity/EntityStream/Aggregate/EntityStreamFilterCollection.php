<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<EntityStreamFilterEntity>
 */
#[Package('buyers-experience')]
class EntityStreamFilterCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EntityStreamFilterEntity::class;
    }
}
