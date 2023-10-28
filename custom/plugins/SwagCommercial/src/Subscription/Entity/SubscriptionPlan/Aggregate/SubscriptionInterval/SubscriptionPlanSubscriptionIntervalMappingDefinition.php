<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionInterval;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPlanSubscriptionIntervalMappingDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'subscription_plan_interval_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('subscription_interval_id', 'subscriptionIntervalId', SubscriptionIntervalDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('subscription_plan_id', 'subscriptionPlanId', SubscriptionPlanDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),

            (new ManyToOneAssociationField('subscriptionPlan', 'subscription_plan_id', SubscriptionPlanDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('subscriptionInterval', 'subscription_interval_id', SubscriptionIntervalDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}
