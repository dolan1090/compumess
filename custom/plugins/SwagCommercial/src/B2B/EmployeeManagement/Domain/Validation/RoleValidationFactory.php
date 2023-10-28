<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Package('checkout')]
class RoleValidationFactory implements DataValidationFactoryInterface
{
    /**
     * @internal
     */
    public function __construct(
    ) {
    }

    public function create(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('role.create'), $context);
    }

    public function update(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('role.update'), $context);
    }

    private function buildCommonValidation(
        DataValidationDefinition $definition,
        SalesChannelContext $context
    ): DataValidationDefinition {
        $definition
            ->add('name', new NotBlank())
            ->add('businessPartnerCustomerId', new NotBlank(), new EntityExists([
                'entity' => CustomerDefinition::ENTITY_NAME,
                'context' => $context->getContext(),
            ]));

        return $definition;
    }
}
