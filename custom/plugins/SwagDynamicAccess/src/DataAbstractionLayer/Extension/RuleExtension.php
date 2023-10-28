<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\Extension;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\DynamicAccess\DataAbstractionLayer\CategoryRule\CategoryRuleDefinition;
use Swag\DynamicAccess\DataAbstractionLayer\LandingPageRule\LandingPageRuleDefinition;
use Swag\DynamicAccess\DataAbstractionLayer\ProductRule\ProductRuleDefinition;

class RuleExtension extends EntityExtension
{
    public const PRODUCT_EXTENSION = 'swagDynamicAccessProducts';
    public const CATEGORY_EXTENSION = 'swagDynamicAccessCategories';
    public const LANDING_PAGE_EXTENSION = 'swagDynamicAccessLandingPages';

    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                self::PRODUCT_EXTENSION,
                ProductDefinition::class,
                ProductRuleDefinition::class,
                RuleDefinition::ENTITY_NAME . '_id',
                ProductDefinition::ENTITY_NAME . '_id'
            ))->addFlags(new CascadeDelete(), new RuleAreas(RuleAreas::PRODUCT_AREA))
        );

        $collection->add(
            (new ManyToManyAssociationField(
                self::CATEGORY_EXTENSION,
                CategoryDefinition::class,
                CategoryRuleDefinition::class,
                RuleDefinition::ENTITY_NAME . '_id',
                CategoryDefinition::ENTITY_NAME . '_id'
            ))->addFlags(new CascadeDelete(), new RuleAreas(RuleAreas::CATEGORY_AREA))
        );

        $collection->add(
            (new ManyToManyAssociationField(
                self::LANDING_PAGE_EXTENSION,
                LandingPageDefinition::class,
                LandingPageRuleDefinition::class,
                RuleDefinition::ENTITY_NAME . '_id',
                LandingPageDefinition::ENTITY_NAME . '_id'
            ))->addFlags(new CascadeDelete(), new RuleAreas(RuleAreas::LANDING_PAGE_AREA))
        );
    }
}
