<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeAccountStatusChangedEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeInviteService;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation\EmailValidationService;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['api']])]
class EmployeeController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $employeeRepository,
        private readonly EntityRepository $customerRepository,
        private readonly EmployeeInviteService $employeeInviteService,
        private readonly EmailValidationService $emailValidationService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(
        path: '/api/_action/create-employee',
        name: 'commercial.api.create-employee',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function createEmployee(Request $request, Context $context): JsonResponse
    {
        /** @var array<string, string> $data */
        $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || !\array_key_exists('email', $data) || !\is_string($data['email'])) {
            throw EmployeeManagementException::invalidRequestArgument('The employee email has to be filled.');
        }

        $salesChannelId = null;
        if (isset($data['businessPartnerCustomerId']) && \is_string($data['businessPartnerCustomerId'])) {
            $salesChannelId = $this->getSalesChannelId($data['businessPartnerCustomerId'], $context);
        }

        if (
            !$this->emailValidationService->validateEmployees($data['email'], $salesChannelId)
            || !$this->emailValidationService->validateCustomers($data['email'], $salesChannelId)
        ) {
            throw EmployeeManagementException::employeeMailNotUnique();
        }

        $event = $this->employeeRepository->create([$data], $context);

        return new JsonResponse($event->getPrimaryKeys(EmployeeDefinition::ENTITY_NAME));
    }

    #[Route(
        path: '/api/_action/update-employee',
        name: 'commercial.api.update-employee',
        methods: ['PATCH'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function updateEmployee(Request $request, Context $context): NoContentResponse
    {
        $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || !\array_key_exists('id', $data) || !\is_string($data['id'])) {
            throw EmployeeManagementException::invalidRequestArgument('The employee id has to be filled.');
        }

        $criteria = new Criteria([$data['id']]);
        $criteria->addAssociation('businessPartnerCustomer.salesChannel');

        $employee = $this->employeeRepository->search($criteria, $context)->first();

        if (!$employee instanceof EmployeeEntity) {
            throw EmployeeManagementException::employeeNotFound();
        }

        if (isset($data['email'])) {
            $id = $data['id'];
            $salesChannelId = null;
            if (isset($data['businessPartnerCustomerId']) && \is_string($data['businessPartnerCustomerId'])) {
                $salesChannelId = $this->getSalesChannelId($data['businessPartnerCustomerId'], $context);
            }

            if (!$this->emailValidationService->validateEmployees($data['email'], $salesChannelId, $id)
                || !$this->emailValidationService->validateCustomers($data['email'], $salesChannelId)) {
                throw EmployeeManagementException::employeeMailNotUnique();
            }
        }

        $this->employeeRepository->update([$data], $context);

        $this->triggerEventIfStatusChanged($data, $employee, $context);

        return new NoContentResponse();
    }

    #[Route(
        path: '/api/_action/invite-employee',
        name: 'commercial.api.invite-employee',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function invite(Request $request, Context $context): NoContentResponse
    {
        $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($data) || !\array_key_exists('id', $data) || !\is_string($data['id'])) {
            throw EmployeeManagementException::employeeIdParameterMissing();
        }

        $employee = $this->employeeRepository->search(new Criteria([$data['id']]), $context)->first();

        if (!$employee instanceof EmployeeEntity) {
            throw EmployeeManagementException::employeeNotFound();
        }

        $this->employeeInviteService->invite($employee, $context);

        return new NoContentResponse();
    }

    private function getSalesChannelId(string $businessPartnerCustomerId, Context $context): ?string
    {
        $salesChannelId = null;
        $customer = $this->customerRepository->search(new Criteria([$businessPartnerCustomerId]), $context)->first();
        if ($customer instanceof CustomerEntity) {
            $salesChannelId = $customer->getBoundSalesChannelId();
        }

        return $salesChannelId;
    }

    /**
     * @param array<string, string> $data
     */
    private function triggerEventIfStatusChanged(array $data, EmployeeEntity $employee, Context $context): void
    {
        if (!isset($data['active'])) {
            return;
        }

        $salesChannel = $employee->getBusinessPartnerCustomer()?->getSalesChannel();

        if (!$salesChannel instanceof SalesChannelEntity || $employee->isActive() === $data['active']) {
            return;
        }

        $event = new EmployeeAccountStatusChangedEvent($context, $salesChannel, $employee);

        $this->eventDispatcher->dispatch($event);
    }
}
