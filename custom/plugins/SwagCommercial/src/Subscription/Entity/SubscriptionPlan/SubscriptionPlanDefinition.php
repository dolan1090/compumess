<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\Product\SubscriptionPlanProductMappingDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionInterval\SubscriptionPlanSubscriptionIntervalMappingDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation\SubscriptionPlanTranslationDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPlanDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'subscription_plan';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SubscriptionPlanEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SubscriptionPlanCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
            'activeStorefrontLabel' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new TranslatedField('name'))->addFlags(new Required(), new ApiAware()),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new Required(), new ApiAware()),
            (new IntField('minimum_execution_count', 'minimumExecutionCount'))->addFlags(new ApiAware()),
            (new BoolField('active_storefront_label', 'activeStorefrontLabel'))->addFlags(new Required(), new ApiAware()),
            (new FkField('availability_rule_id', 'availabilityRuleId', RuleDefinition::class))->addFlags(new ApiAware()),
            (new FloatField('discount_percentage', 'discountPercentage'))->addFlags(new ApiAware()),
            (new TranslatedField('label'))->addFlags(new ApiAware()),

            (new ManyToOneAssociationField(
                'availabilityRule',
                'availability_rule_id',
                RuleDefinition::class,
            ))->addFlags(),

            (new ManyToManyAssociationField(
                'subscriptionIntervals',
                SubscriptionIntervalDefinition::class,
                SubscriptionPlanSubscriptionIntervalMappingDefinition::class,
                'subscription_plan_id',
                'subscription_interval_id',
            ))->addFlags(),

            (new ManyToManyAssociationField(
                'products',
                ProductDefinition::class,
                SubscriptionPlanProductMappingDefinition::class,
                'subscription_plan_id',
                'product_id',
            ))->addFlags(),

            (new OneToManyAssociationField(
                'subscriptions',
                SubscriptionDefinition::class,
                'subscription_plan_id'
            )
            )->addFlags(new ApiAware()),

            (new TranslationsAssociationField(
                SubscriptionPlanTranslationDefinition::class,
                'subscription_plan_id'
            ))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
