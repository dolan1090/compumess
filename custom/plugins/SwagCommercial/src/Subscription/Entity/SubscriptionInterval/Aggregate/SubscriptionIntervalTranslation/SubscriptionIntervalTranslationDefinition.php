<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionIntervalTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'subscription_interval_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SubscriptionIntervalTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SubscriptionIntervalTranslationCollection::class;
    }

    public function getParentDefinitionClass(): string
    {
        return SubscriptionIntervalDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required(), new ApiAware()),
        ]);
    }
}
