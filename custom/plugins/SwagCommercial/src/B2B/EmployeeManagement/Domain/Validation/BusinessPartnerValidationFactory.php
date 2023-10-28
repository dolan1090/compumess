<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class BusinessPartnerValidationFactory implements DataValidationFactoryInterface
{
    /**
     * @internal
     */
    public function __construct(
    ) {
    }

    public function create(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('business_partner.create'), $context);
    }

    public function update(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('business_partner.update'), $context);
    }

    private function buildCommonValidation(
        DataValidationDefinition $definition,
        SalesChannelContext $context,
    ): DataValidationDefinition {
        $definition
            ->add('defaultRoleId', new EntityExists([
                'entity' => RoleDefinition::ENTITY_NAME,
                'context' => $context->getContext(),
            ]));

        return $definition;
    }
}
