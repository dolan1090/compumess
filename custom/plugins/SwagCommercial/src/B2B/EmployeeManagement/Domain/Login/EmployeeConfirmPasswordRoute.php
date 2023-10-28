<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Login;

use Composer\Semver\Constraint\ConstraintInterface;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\AbstractEmployeeConfirmPasswordRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SuccessResponse;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['store-api'], '_contextTokenRequired' => true])]
#[Package('checkout')]
class EmployeeConfirmPasswordRoute extends AbstractEmployeeConfirmPasswordRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $employeeRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator,
    ) {
    }

    public function getDecorated(): AbstractEmployeeConfirmPasswordRoute
    {
        throw EmployeeManagementException::decorationPattern(self::class);
    }

    #[Route(path: '/store-api/account/business-partner/employee/confirm/password', name: 'store-api.account.business-partner.employee.confirm.password', methods: ['POST'])]
    public function confirmPassword(RequestDataBag $data, SalesChannelContext $context): SuccessResponse
    {
        $hash = $data->get('hash');

        if (!\is_string($hash)) {
            throw EmployeeManagementException::invalidRequestArgument('Parameter hash must be a string');
        }

        $this->validateResetPassword($data, $context);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('recoveryHash', $hash));
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var EmployeeEntity|null $employee */
        $employee = $this->employeeRepository->search($criteria, $context->getContext())->first();

        $validDateTime = (new \DateTime())->sub(new \DateInterval('PT2H'));

        if (!$employee || $employee->getRecoveryTime() < $validDateTime) {
            throw EmployeeManagementException::hashExpired($hash);
        }

        $this->employeeRepository->update([
            [
                'id' => $employee->getId(),
                'password' => $data->get('newPassword'),
                'recoveryTime' => null,
                'recoveryHash' => null,
            ],
        ], $context->getContext());

        return new SuccessResponse();
    }

    private function validateResetPassword(DataBag $data, SalesChannelContext $context): void
    {
        $definition = new DataValidationDefinition('employee.password.update');

        $minPasswordLength = $this->systemConfigService->get('b2b.employee.passwordMinLength', $context->getSalesChannel()->getId());

        $definition->add('newPassword', new NotBlank(), new Length(['min' => $minPasswordLength]), new EqualTo(['propertyPath' => 'newPasswordConfirm']));

        $this->dispatchValidationEvent($definition, $data, $context->getContext());

        /** @var array<string> $dataBag */
        $dataBag = $data->all();
        $this->validator->validate($dataBag, $definition);

        $this->tryValidateEqualtoConstraint($dataBag, 'newPassword', $definition);
    }

    private function dispatchValidationEvent(DataValidationDefinition $definition, DataBag $data, Context $context): void
    {
        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
    }

    /**
     * @param string[] $data
     *
     *@throws ConstraintViolationException
     */
    private function tryValidateEqualtoConstraint(array $data, string $field, DataValidationDefinition $validation): void
    {
        $validations = $validation->getProperties();

        if (!\array_key_exists($field, $validations)) {
            return;
        }

        /** @var array<ConstraintInterface> $fieldValidations */
        $fieldValidations = $validations[$field];

        /** @var EqualTo|null $equalityValidation */
        $equalityValidation = null;

        foreach ($fieldValidations as $emailValidation) {
            if ($emailValidation instanceof EqualTo) {
                $equalityValidation = $emailValidation;

                break;
            }
        }

        if (!$equalityValidation instanceof EqualTo) {
            return;
        }

        $compareValue = $data[$equalityValidation->propertyPath] ?? null;
        if ($data[$field] === $compareValue) {
            return;
        }

        /** @var string $compareValue */
        $message = str_replace('{{ compared_value }}', $compareValue, (string) $equalityValidation->message);

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation($message, $equalityValidation->message, [], '', $field, $data[$field], null, 'passwordDoNotMatch'));

        throw new ConstraintViolationException($violations, $data);
    }
}
