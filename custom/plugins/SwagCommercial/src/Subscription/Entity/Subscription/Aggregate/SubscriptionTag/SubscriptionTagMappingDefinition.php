<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionTag;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Tag\TagDefinition;

#[Package('checkout')]
class SubscriptionTagMappingDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'subscription_tag_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('subscription_id', 'subscriptionId', SubscriptionDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('tag_id', 'tagId', TagDefinition::class))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),

            (new ManyToOneAssociationField('subscription', 'subscriptionId', SubscriptionDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('tag', 'tag_id', TagDefinition::class, 'id', false))->addFlags(new ApiAware()),
        ]);
    }
}
