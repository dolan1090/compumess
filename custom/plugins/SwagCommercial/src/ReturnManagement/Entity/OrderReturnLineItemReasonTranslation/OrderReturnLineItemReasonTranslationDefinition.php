<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderReturnLineItemReasonTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'order_return_line_item_reason_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderReturnLineItemReasonTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderReturnLineItemReasonTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return OrderReturnLineItemReasonDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('content', 'content'))->addFlags(new Required()),
        ]);
    }
}
