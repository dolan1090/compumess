<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @internal
 */
#[Package('checkout')]
class DecoratedCustomerEmailUniqueValidator extends ConstraintValidator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ConstraintValidator $decorated,
        private readonly EmailValidationService $emailValidationService,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $this->decorated->initialize($this->context);
        $this->decorated->validate($value, $constraint);

        if (!$constraint instanceof CustomerEmailUnique) {
            throw EmployeeManagementException::unexpectedType($constraint, CustomerEmailUnique::class);
        }

        $salesChannelId = null;
        if ($this->systemConfigService->get('core.systemWideLoginRegistration.isCustomerBoundToSalesChannel')) {
            $salesChannelId = $constraint->getSalesChannelContext()->getSalesChannelId();
        }

        if (
            !\is_string($value)
            || (
                $this->emailValidationService->validateEmployees($value, $salesChannelId)
                && $this->emailValidationService->validateCustomers($value, $salesChannelId)
            )
        ) {
            return;
        }

        foreach ($this->context->getViolations() as $violation) {
            if ($violation->getCode() === CustomerEmailUnique::CUSTOMER_EMAIL_NOT_UNIQUE) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ email }}', $this->formatValue($value))
            ->setCode(CustomerEmailUnique::CUSTOMER_EMAIL_NOT_UNIQUE)
            ->addViolation();
    }
}
