<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class RuleSubscriptionExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new OneToManyAssociationField(
            'subscriptionPlans',
            SubscriptionPlanDefinition::class,
            'availability_rule_id',
        ));

        $collection->add(new OneToManyAssociationField(
            'subscriptionIntervals',
            SubscriptionIntervalDefinition::class,
            'availability_rule_id',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }
}
