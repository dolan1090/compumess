<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation\OrderReturnLineItemReasonTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderReturnLineItemReasonDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_return_line_item_reason';
    final public const DEFAULT_REASON_KEY = 'others';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderReturnLineItemReasonCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderReturnLineItemReasonEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new StringField('reason_key', 'reasonKey'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('content'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslationsAssociationField(OrderReturnLineItemReasonTranslationDefinition::class, 'order_return_line_item_reason_id'))->addFlags(new Required()),
            (new OneToManyAssociationField('lineItems', OrderReturnLineItemDefinition::class, 'reason_id', 'id'))->addFlags(new RestrictDelete()),
        ]);
    }
}
