<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<EntityStreamEntity>
 */
#[Package('buyers-experience')]
class EntityStreamCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return EntityStreamEntity::class;
    }
}
