<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\Product\SubscriptionPlanProductMappingDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class ProductSubscriptionExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'subscriptionPlans',
                SubscriptionPlanDefinition::class,
                SubscriptionPlanProductMappingDefinition::class,
                'product_id',
                'subscription_plan_id'
            ))->addFlags(new CascadeDelete(), new Inherited())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
