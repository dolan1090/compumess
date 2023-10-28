<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AdvancedSearchConfigFieldEntity>
 */
#[Package('buyers-experience')]
class AdvancedSearchConfigFieldCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'advanced_search_config_field_collection';
    }

    protected function getExpectedClass(): string
    {
        return AdvancedSearchConfigFieldEntity::class;
    }
}
