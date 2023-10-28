<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Acris\CustomerPrice\Custom\Aggregate\CustomerPriceRule\CustomerPriceRuleDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CustomerPriceDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'acris_customer_price';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CustomerPriceCollection::class;
    }

    public function getEntityClass(): string
    {
        return CustomerPriceEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            new BoolField('active', 'active'),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
            (new StringField('list_price_type','listPriceType')),
            new DateTimeField('active_from', 'activeFrom'),
            new DateTimeField('active_until', 'activeUntil'),

            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class))->addFlags(new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new OneToManyAssociationField('acrisPrices', CustomerAdvancedPriceDefinition::class, 'customer_price_id'))->addFlags(new CascadeDelete()),
            new ManyToManyAssociationField('rules', RuleDefinition::class, CustomerPriceRuleDefinition::class, 'customer_price_id', 'rule_id'),
            (new ManyToManyIdField('rule_ids', 'ruleIds', 'rules')),
            new CustomFields(),
        ]);
    }
}
