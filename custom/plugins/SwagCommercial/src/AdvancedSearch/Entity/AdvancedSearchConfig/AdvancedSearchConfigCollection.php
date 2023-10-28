<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AdvancedSearchConfigEntity>
 */
#[Package('buyers-experience')]
class AdvancedSearchConfigCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'advanced_search_config_collection';
    }

    protected function getExpectedClass(): string
    {
        return AdvancedSearchConfigEntity::class;
    }
}
