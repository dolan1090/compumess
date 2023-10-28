<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Extension;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

/**
 * @final tag:v6.5.0
 *
 * @internal
 */
#[Package('checkout')]
class OrderLineItemExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderLineItemDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('returns', OrderReturnLineItemDefinition::class, 'order_line_item_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),
        );

        $collection->add((new FkField('state_id', 'stateId', StateMachineStateDefinition::class))->addFlags(new ApiAware()));
        $collection->add((new ManyToOneAssociationField('state', 'state_id', StateMachineStateDefinition::class, 'id', false))->addFlags(new ApiAware()));
    }
}
