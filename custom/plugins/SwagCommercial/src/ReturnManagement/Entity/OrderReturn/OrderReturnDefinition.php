<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturn;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CartPriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedByField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedByField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;
use Shopware\Core\System\User\UserDefinition;

#[Package('checkout')]
class OrderReturnDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'order_return';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderReturnCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderReturnEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return OrderDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),

            (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new ApiAware(), new Required()),
            new ManyToOneAssociationField('order', 'order_id', OrderDefinition::class, 'id', false),

            (new CartPriceField('price', 'price'))->addFlags(new ApiAware()),
            (new CalculatedPriceField('shipping_costs', 'shippingCosts'))->addFlags(new ApiAware()),

            (new StateMachineStateField('state_id', 'stateId', OrderReturnStates::STATE_MACHINE))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('state', 'state_id', StateMachineStateDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new StringField('return_number', 'returnNumber'))->addFlags(new ApiAware(), new Required()),
            (new DateTimeField('requested_at', 'requestedAt'))->addFlags(new ApiAware(), new Required()),

            (new FloatField('amount_total', 'amountTotal'))->addFlags(new ApiAware()),
            (new FloatField('amount_net', 'amountNet'))->addFlags(new ApiAware()),

            new LongTextField('internal_comment', 'internalComment'),
            (new CreatedByField())->addFlags(new ApiAware()),
            (new UpdatedByField())->addFlags(new ApiAware()),

            new ManyToOneAssociationField('createdBy', 'created_by_id', UserDefinition::class, 'id', false),
            new ManyToOneAssociationField('updatedBy', 'updated_by_id', UserDefinition::class, 'id', false),

            (new OneToManyAssociationField('lineItems', OrderReturnLineItemDefinition::class, 'order_return_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);
    }
}
