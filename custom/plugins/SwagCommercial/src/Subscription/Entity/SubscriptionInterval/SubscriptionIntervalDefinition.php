<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation\SubscriptionIntervalTranslationDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionInterval\SubscriptionPlanSubscriptionIntervalMappingDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CronIntervalField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateIntervalField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionIntervalDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'subscription_interval';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SubscriptionIntervalEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SubscriptionIntervalCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
            'dateInterval' => new DateInterval('P1W'),
            'cronInterval' => new CronInterval(CronInterval::EMPTY_EXPRESSION),
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required(), new ApiAware()),
            (new DateIntervalField('date_interval', 'dateInterval'))->addFlags(new Required(), new ApiAware()),
            (new CronIntervalField('cron_interval', 'cronInterval'))->addFlags(new Required(), new ApiAware()),

            (new FkField('availability_rule_id', 'availabilityRuleId', RuleDefinition::class))->addFlags(new ApiAware()),

            (new OneToManyAssociationField(
                'subscriptions',
                SubscriptionDefinition::class,
                'subscription_interval_id'
            ))->addFlags(new ApiAware(), new SetNullOnDelete()),

            (new ManyToOneAssociationField(
                'availabilityRule',
                'availability_rule_id',
                RuleDefinition::class,
            ))->addFlags(),

            (new ManyToManyAssociationField(
                'subscriptionPlans',
                SubscriptionPlanDefinition::class,
                SubscriptionPlanSubscriptionIntervalMappingDefinition::class,
                'subscription_interval_id',
                'subscription_plan_id',
            ))->addFlags(),

            (new TranslationsAssociationField(
                SubscriptionIntervalTranslationDefinition::class,
                'subscription_interval_id'
            ))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
