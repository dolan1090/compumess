<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('buyers-experience')]
class CategoryExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        $collection->add(
            (new JsonField('search', 'search'))->addFlags(new Runtime())
        );
    }

    public function getDefinitionClass(): string
    {
        return CategoryDefinition::class;
    }
}
