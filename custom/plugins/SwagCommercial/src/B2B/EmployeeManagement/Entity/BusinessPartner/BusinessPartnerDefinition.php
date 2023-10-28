<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class BusinessPartnerDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'b2b_business_partner';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return BusinessPartnerCollection::class;
    }

    public function getEntityClass(): string
    {
        return BusinessPartnerEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField('customer_id', 'customerId', CustomerDefinition::class),
            new FkField('default_role_id', 'defaultRoleId', RoleDefinition::class),
            (new CustomFields())->addFlags(new ApiAware()),
            new OneToOneAssociationField('customer', 'customer_id', 'id', CustomerDefinition::class, false),
            new OneToOneAssociationField('defaultRole', 'default_role_id', 'id', RoleDefinition::class, false),
        ]);
    }
}
