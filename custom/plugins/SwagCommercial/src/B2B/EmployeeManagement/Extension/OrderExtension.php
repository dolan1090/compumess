<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Extension;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\Aggregate\OrderEmployeeDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class OrderExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('orderEmployee', OrderEmployeeDefinition::class, 'order_id', 'id'))->addFlags(new CascadeDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }
}
