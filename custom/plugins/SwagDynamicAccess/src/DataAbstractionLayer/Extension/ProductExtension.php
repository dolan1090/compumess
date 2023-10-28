<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\Extension;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\DynamicAccess\DataAbstractionLayer\ProductRule\ProductRuleDefinition;

class ProductExtension extends EntityExtension
{
    public const RULE_EXTENSION = 'swagDynamicAccessRules';

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                self::RULE_EXTENSION,
                RuleDefinition::class,
                ProductRuleDefinition::class,
                ProductDefinition::ENTITY_NAME . '_id',
                RuleDefinition::ENTITY_NAME . '_id'
            ))->addFlags(new CascadeDelete())
        );
    }
}
