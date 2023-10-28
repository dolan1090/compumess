<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Extension\Order;

use Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderProductWarehouse\OrderProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Order\Aggregate\OrderWarehouseGroup\OrderWarehouseGroupDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class OrderExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'warehouseGroups',
                OrderWarehouseGroupDefinition::class,
                'order_id',
                'id'
            ))->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new OneToManyAssociationField(
                'warehouseProducts',
                OrderProductWarehouseDefinition::class,
                'order_id',
                'id'
            ))->addFlags(new CascadeDelete())
        );
    }
}
