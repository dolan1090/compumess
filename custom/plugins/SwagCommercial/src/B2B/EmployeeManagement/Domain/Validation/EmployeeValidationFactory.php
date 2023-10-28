<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Package('checkout')]
class EmployeeValidationFactory implements DataValidationFactoryInterface
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    public function create(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('employee.create'), $context);
    }

    public function update(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('employee.update'), $context);
    }

    private function buildCommonValidation(
        DataValidationDefinition $definition,
        SalesChannelContext $context
    ): DataValidationDefinition {
        $definition
            ->add('firstName', new NotBlank())
            ->add('lastName', new NotBlank())
            ->add('email', new NotBlank(), new Email())
            ->add('active', new Type('boolean'))
            ->add('roleId', new EntityExists([
                'entity' => RoleDefinition::ENTITY_NAME,
                'context' => $context->getContext(),
            ]))
            ->add('businessPartnerCustomerId', new NotBlank(), new EntityExists([
                'entity' => CustomerDefinition::ENTITY_NAME,
                'context' => $context->getContext(),
            ]));

        return $definition;
    }
}
