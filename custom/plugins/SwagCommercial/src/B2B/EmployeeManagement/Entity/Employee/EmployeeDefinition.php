<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\EmailField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PasswordField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class EmployeeDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'b2b_employee';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EmployeeCollection::class;
    }

    public function getEntityClass(): string
    {
        return EmployeeEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            new FkField('business_partner_customer_id', 'businessPartnerCustomerId', CustomerDefinition::class),
            new FkField('role_id', 'roleId', RoleDefinition::class),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new StringField('first_name', 'firstName'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new StringField('last_name', 'lastName'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new EmailField('email', 'email'))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING, false)),
            (new PasswordField('password', 'password', \PASSWORD_DEFAULT, [], PasswordField::FOR_CUSTOMER))->removeFlag(ApiAware::class),
            (new DateTimeField('recovery_time', 'recoveryTime'))->addFlags(new ApiAware(AdminApiSource::class)),
            (new StringField('recovery_hash', 'recoveryHash'))->removeFlag(ApiAware::class),
            (new CustomFields())->addFlags(new ApiAware()),
            new ManyToOneAssociationField('businessPartnerCustomer', 'business_partner_customer_id', CustomerDefinition::class, 'id', false),
            (new ManyToOneAssociationField('role', 'role_id', RoleDefinition::class, 'id', false))->addFlags(new ApiAware(), new SetNullOnDelete()),
        ]);
    }
}
