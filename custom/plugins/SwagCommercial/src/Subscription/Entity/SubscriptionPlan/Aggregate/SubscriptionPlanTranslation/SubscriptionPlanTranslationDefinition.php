<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPlanTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'subscription_plan_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SubscriptionPlanTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SubscriptionPlanTranslationCollection::class;
    }

    public function getParentDefinitionClass(): string
    {
        return SubscriptionPlanDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required(), new ApiAware()),
            (new StringField('description', 'description'))->addFlags(new ApiAware()),
            (new StringField('label', 'label'))->addFlags(new ApiAware()),
        ]);
    }
}
