<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\CategoryRule;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class CategoryRuleDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'swag_dynamic_access_category_rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CategoryRuleCollection::class;
    }

    public function getEntityClass(): string
    {
        return CategoryRuleEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField(
                CategoryDefinition::ENTITY_NAME . '_id',
                'categoryId',
                CategoryDefinition::class
            ))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ReferenceVersionField(CategoryDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'category',
                CategoryDefinition::ENTITY_NAME . '_id',
                CategoryDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete(), new ApiAware()),

            (new FkField(
                RuleDefinition::ENTITY_NAME . '_id',
                'ruleId',
                RuleDefinition::class
            ))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'rule',
                RuleDefinition::ENTITY_NAME . '_id',
                RuleDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete(), new ApiAware()),
        ]);
    }
}
