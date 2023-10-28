<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\EmployeeListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\EmployeeResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\BaseEmployeePermissions;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeAccountStatusChangedEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeInviteService;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation\UrlValidator;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagement;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api'], '_b2bFeatureCode' => EmployeeManagement::FEATURE_CODE])]
#[Package('checkout')]
class EmployeeRoute extends AbstractEmployeeRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $employeeRepository,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $factory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EmployeeInviteService $employeeInviteService
    ) {
    }

    public function getDecorated(): AbstractEmployeeRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/employee/create',
        name: 'commercial.store-api.employee.create',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_CREATE]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function create(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        /** @var string|null $url */
        $url = $request->get('storefrontUrl');
        UrlValidator::validateStorefrontUrl($url, $context);

        $hash = Random::getAlphanumericString(32);
        $now = new \DateTimeImmutable();

        $data = [
            'businessPartnerCustomerId' => $businessPartner->getCustomerId(),
            'roleId' => $request->get('roleId') ?: null,
            'firstName' => $request->get('firstName'),
            'lastName' => $request->get('lastName'),
            'email' => $request->get('email'),
            'recoveryTime' => $now->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'recoveryHash' => $hash,
        ];

        $this->validate($data, $context);

        $result = $this->employeeRepository->create([$data], $context->getContext());

        $response = $this->getEmployeeResponse($result, $context->getContext());

        $this->employeeInviteService->invite($response->getEmployee(), $context->getContext());

        return $response;
    }

    #[Route(
        path: '/store-api/employee',
        name: 'commercial.store-api.employee.list',
        defaults: ['_entity' => 'b2b_employee', '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_READ]],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function list(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeListResponse
    {
        /** @var int $limit */
        $limit = $request->get('limit', 10);
        /** @var int $page */
        $page = $request->get('p', 1);

        $criteria = new Criteria();

        $criteria
            ->addAssociation('role')
            ->setLimit($limit)
            ->setOffset(($page - 1) * $limit)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
            ->addSorting(new FieldSorting('lastName', FieldSorting::ASCENDING))
            ->addSorting(new FieldSorting('firstName', FieldSorting::ASCENDING))
            ->addFilter(new EqualsFilter('businessPartnerCustomerId', $businessPartner->getCustomerId()))
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        $searchResult = $this->employeeRepository->search($criteria, $context->getContext());

        return new EmployeeListResponse($searchResult);
    }

    #[Route(
        path: '/store-api/employee/{id}',
        name: 'commercial.store-api.employee.get',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_READ]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function get(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        $criteria = (new Criteria([$id]))
            ->addAssociation('businessPartnerCustomer.salutation')
            ->addFilter(new EqualsFilter('businessPartnerCustomerId', $businessPartner->getCustomerId()));

        $employee = $this->employeeRepository->search($criteria, $context->getContext())->first();

        if (!$employee instanceof EmployeeEntity) {
            throw EmployeeManagementException::employeeNotFound();
        }

        return new EmployeeResponse($employee);
    }

    #[Route(
        path: '/store-api/employee/{id}',
        name: 'commercial.store-api.employee.edit',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT]],
        methods: ['PATCH'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function edit(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        $businessPartnerCustomerId = $businessPartner->getCustomerId();
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter(
            'businessPartnerCustomerId',
            $businessPartnerCustomerId,
        ));

        $employee = $this->employeeRepository
            ->search($criteria, $context->getContext())
            ->first();

        if (!$employee instanceof EmployeeEntity) {
            throw EmployeeManagementException::employeeNotFound();
        }

        $data = [
            'id' => $id,
            'businessPartnerCustomerId' => $businessPartnerCustomerId,
            'roleId' => $request->get('roleId') ?: null,
            'firstName' => $request->get('firstName'),
            'lastName' => $request->get('lastName'),
            'email' => $request->get('email'),
        ];

        if ($request->request->has('active')) {
            $data['active'] = $request->request->getBoolean('active');
        }

        $password = $request->get('password');
        if ($password !== null) {
            $data['password'] = $password;
        }

        $this->validate($data, $context, $employee);

        $result = $this->employeeRepository->update([$data], $context->getContext());

        return $this->getEmployeeResponse($result, $context->getContext());
    }

    #[Route(
        path: '/store-api/employee/activate/{id}',
        name: 'commercial.store-api.employee.activate',
        defaults: ['_loginRequired' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT]],
        methods: ['PATCH'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function activate(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        return $this->toggleActive($id, true, $context, $businessPartner);
    }

    #[Route(
        path: '/store-api/employee/deactivate/{id}',
        name: 'commercial.store-api.employee.deactivate',
        defaults: ['_loginRequired' => true, '_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_EDIT]],
        methods: ['PATCH'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function deactivate(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        return $this->toggleActive($id, false, $context, $businessPartner);
    }

    #[Route(
        path: '/store-api/employee/{id}',
        name: 'commercial.store-api.employee.delete',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_DELETE]],
        methods: ['DELETE'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function delete(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): NoContentResponse
    {
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter(
            'businessPartnerCustomerId',
            $businessPartner->getCustomerId()
        ));

        $isAssignedToBusinessPartner = (bool) $this->employeeRepository
            ->searchIds($criteria, $context->getContext())
            ->getIds();

        if (!$isAssignedToBusinessPartner) {
            throw EmployeeManagementException::employeeNotFound();
        }

        $this->employeeRepository->delete([['id' => $id]], $context->getContext());

        return new NoContentResponse();
    }

    #[Route(
        path: '/store-api/employee/reinvite/{id}',
        name: 'commercial.store-api.employee.reinvite',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::EMPLOYEE_CREATE]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function reinvite(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        /** @var string|null $url */
        $url = $request->get('storefrontUrl');
        UrlValidator::validateStorefrontUrl($url, $context);

        $employee = $this->get($id, $context, $businessPartner)->getEmployee();

        $this->employeeInviteService->invite($employee, $context->getContext());

        return new EmployeeResponse($employee);
    }

    private function toggleActive(string $id, bool $active, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse
    {
        $businessPartnerCustomerId = $businessPartner->getCustomerId();
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter(
            'businessPartnerCustomerId',
            $businessPartnerCustomerId,
        ));

        $employee = $this->employeeRepository
            ->search($criteria, $context->getContext())
            ->first();

        if (!$employee instanceof EmployeeEntity) {
            throw EmployeeManagementException::employeeNotFound();
        }

        $data = [
            'id' => $id,
            'businessPartnerCustomerId' => $businessPartnerCustomerId,
            'active' => $active,
        ];

        $result = $this->employeeRepository->update([$data], $context->getContext());

        $event = new EmployeeAccountStatusChangedEvent($context->getContext(), $context->getSalesChannel(), $employee);
        $this->eventDispatcher->dispatch($event);

        return $this->getEmployeeResponse($result, $context->getContext());
    }

    private function getEmployeeResponse(EntityWrittenContainerEvent $event, Context $context): EmployeeResponse
    {
        $keys = $event->getPrimaryKeys(EmployeeDefinition::ENTITY_NAME);
        $criteria = new Criteria($keys);
        $criteria->addAssociation('businessPartnerCustomer.salutation');

        /** @var EmployeeEntity $employee */
        $employee = $this->employeeRepository
            ->search($criteria, $context)
            ->first();

        return new EmployeeResponse($employee);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validate(array $data, SalesChannelContext $context, ?EmployeeEntity $employee = null): void
    {
        $validation = $this->factory->create($context);

        $newMail = $data['email'] ?? null;
        if (!$employee || (\is_string($newMail) && mb_strtolower($employee->getEmail()) !== mb_strtolower($newMail))) {
            $validation->add(
                'email',
                new CustomerEmailUnique(['context' => $context->getContext(), 'salesChannelContext' => $context])
            );
        }

        $validationEvent = new BuildValidationEvent($validation, new DataBag($data), $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $violations = $this->validator->getViolations($data, $validation);

        if ($violations->count()) {
            throw new ConstraintViolationException($violations, $data);
        }
    }
}
