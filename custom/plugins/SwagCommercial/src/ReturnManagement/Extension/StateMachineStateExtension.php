<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Extension;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
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
class StateMachineStateExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return StateMachineStateDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('orderReturns', OrderReturnDefinition::class, 'state_id'))->addFlags(new RestrictDelete())
        );

        $collection->add(
            (new OneToManyAssociationField('orderReturnLineItems', OrderReturnLineItemDefinition::class, 'state_id'))->addFlags(new RestrictDelete())
        );

        $collection->add(
            (new OneToManyAssociationField('orderLineItems', OrderLineItemDefinition::class, 'state_id'))->addFlags(new RestrictDelete()),
        );
    }
}
