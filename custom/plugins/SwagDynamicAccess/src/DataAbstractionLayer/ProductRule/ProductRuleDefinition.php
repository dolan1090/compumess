<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\ProductRule;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class ProductRuleDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'swag_dynamic_access_product_rule';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductRuleCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductRuleEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField(
                ProductDefinition::ENTITY_NAME . '_id',
                'productId',
                ProductDefinition::class
            ))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new ManyToOneAssociationField(
                'product',
                ProductDefinition::ENTITY_NAME . '_id',
                ProductDefinition::class,
                'id',
                false
            ))->addFlags(new ApiAware()),

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
            ))->addFlags(new ApiAware()),
        ]);
    }
}
