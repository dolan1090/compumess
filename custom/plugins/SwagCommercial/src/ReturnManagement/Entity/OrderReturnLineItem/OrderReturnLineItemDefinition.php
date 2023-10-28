<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

#[Package('checkout')]
class OrderReturnLineItemDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_return_line_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderReturnLineItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderReturnLineItemEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderReturnDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),

            (new FkField('order_return_id', 'orderReturnId', OrderReturnDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderReturnDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('order_line_item_id', 'orderLineItemId', OrderLineItemDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderLineItemDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('reason_id', 'reasonId', OrderReturnLineItemReasonDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('reason', 'reason_id', OrderReturnLineItemReasonDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new IntField('quantity', 'quantity'))->addFlags(new ApiAware(), new Required()),

            (new CalculatedPriceField('price', 'price'))->addFlags(new Required()),
            (new FloatField('refund_amount', 'refundAmount'))->addFlags(new ApiAware()),
            (new IntField('restock_quantity', 'restockQuantity'))->addFlags(new ApiAware()),

            new LongTextField('internal_comment', 'internalComment'),
            (new CustomFields())->addFlags(new ApiAware()),

            (new FkField('state_id', 'stateId', StateMachineStateDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('state', 'state_id', StateMachineStateDefinition::class, 'id', false))->addFlags(new ApiAware()),

            new ManyToOneAssociationField('return', 'order_return_id', OrderReturnDefinition::class, 'id', false),
            new ManyToOneAssociationField('lineItem', 'order_line_item_id', OrderLineItemDefinition::class, 'id', false),
        ]);
    }
}
