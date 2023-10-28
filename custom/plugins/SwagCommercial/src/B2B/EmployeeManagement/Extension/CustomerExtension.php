<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Extension;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class CustomerExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('employees', EmployeeDefinition::class, 'business_partner_customer_id', 'id'))->addFlags(new CascadeDelete())
        );
        $collection->add(
            (new OneToManyAssociationField('roles', RoleDefinition::class, 'business_partner_customer_id', 'id'))->addFlags(new CascadeDelete())
        );
        $collection->add(
            (new OneToOneAssociationField('b2bBusinessPartner', 'id', 'customer_id', BusinessPartnerDefinition::class, true))->addFlags(new CascadeDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return CustomerDefinition::class;
    }
}
