<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Acris\CustomerPrice\Custom\Aggregate\CustomerPriceRule\CustomerPriceRuleDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RuleAreas;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RuleExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'acrisCustomerPrices',
                CustomerPriceDefinition::class,
                CustomerPriceRuleDefinition::class,
                'rule_id',
                'customer_price_id'
            ))->addFlags(new RuleAreas(RuleAreas::PRODUCT_AREA))
        );
    }

    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }
}
