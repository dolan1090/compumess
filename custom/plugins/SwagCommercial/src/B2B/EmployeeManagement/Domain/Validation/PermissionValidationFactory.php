<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Package('checkout')]
class PermissionValidationFactory implements DataValidationFactoryInterface
{
    public function create(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('permission.create'));
    }

    public function update(SalesChannelContext $context): DataValidationDefinition
    {
        return $this->buildCommonValidation(new DataValidationDefinition('permission.update'));
    }

    private function buildCommonValidation(DataValidationDefinition $definition): DataValidationDefinition
    {
        return $definition
            ->add('name', new NotBlank())
            ->add('group', new NotBlank());
    }
}
