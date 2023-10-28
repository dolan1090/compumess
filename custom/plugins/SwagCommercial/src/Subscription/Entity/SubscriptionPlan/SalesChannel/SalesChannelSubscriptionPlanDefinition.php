<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SalesChannel;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class SalesChannelSubscriptionPlanDefinition extends SubscriptionPlanDefinition implements SalesChannelDefinitionInterface
{
    public function getEntityClass(): string
    {
        return SalesChannelSubscriptionPlanEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SalesChannelSubscriptionPlanCollection::class;
    }

    public function processCriteria(Criteria $criteria, SalesChannelContext $context): void
    {
    }

    protected function defineFields(): FieldCollection
    {
        $fields = parent::defineFields();

        $fields->add(
            (new PriceField('discount_price', 'discountPrice'))->addFlags(new ApiAware(), new Runtime(['discountPercentage']))
        );

        return $fields;
    }
}
