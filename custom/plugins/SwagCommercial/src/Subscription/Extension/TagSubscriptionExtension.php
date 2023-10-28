<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionTag\SubscriptionTagMappingDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Tag\TagDefinition;

#[Package('checkout')]
class TagSubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return TagDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'subscriptions',
                SubscriptionDefinition::class,
                SubscriptionTagMappingDefinition::class,
                'tag_id',
                'subscription_id'
            ))->addFlags(new CascadeDelete()),
        );
    }
}
