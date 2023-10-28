<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\RoleListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\RoleResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\BaseEmployeePermissions;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagement;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
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
class RoleRoute extends AbstractRoleRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $roleRepository,
        private readonly EntityRepository $businessPartnerRepository,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $roleValidationFactory,
        private readonly DataValidationFactoryInterface $businessPartnerValidationFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getDecorated(): AbstractRoleRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/role/create',
        name: 'commercial.store-api.role.create',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_CREATE]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function create(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse
    {
        $data = [
            'name' => $request->get('name'),
            'businessPartnerCustomerId' => $businessPartner->getCustomerId(),
            'permissions' => $request->get('permissions'),
        ];
        $this->validateRole($context, $data);
        $result = $this->roleRepository->create([$data], $context->getContext());

        $isDefaultRole = (bool) $request->get('isDefaultRole');
        if ($isDefaultRole) {
            $createdRoleId = $result->getPrimaryKeys(RoleDefinition::ENTITY_NAME)[0];
            $this->setDefaultRoleId($createdRoleId, $context, $businessPartner);
        }

        return $this->getRoleResponse($result, $context->getContext());
    }

    #[Route(
        path: '/store-api/role',
        name: 'commercial.store-api.role.list',
        defaults: ['_entity' => 'b2b_components_role', '_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_READ]],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function list(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleListResponse
    {
        /** @var int $limit */
        $limit = $request->get('limit', 10);
        /** @var int $page */
        $page = $request->get('p', 1);

        $criteria = new Criteria();

        $criteria
            ->setLimit($limit)
            ->setOffset(($page - 1) * $limit)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
            ->addSorting(new FieldSorting('name', FieldSorting::ASCENDING))
            ->addAssociation('employees')
            ->addFilter(new EqualsFilter('businessPartnerCustomerId', $businessPartner->getCustomerId()));

        $searchResult = $this->roleRepository->search($criteria, $context->getContext());

        return new RoleListResponse($searchResult);
    }

    #[Route(
        path: '/store-api/role/{id}',
        name: 'commercial.store-api.role.get',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_READ]],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function get(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse
    {
        $criteria = (new Criteria([$id]))
            ->addAssociation('employees')
            ->addFilter(new EqualsFilter('businessPartnerCustomerId', $businessPartner->getCustomerId()));

        $role = $this->roleRepository->search($criteria, $context->getContext())->first();

        if (!$role instanceof RoleEntity) {
            throw EmployeeManagementException::roleNotFound();
        }

        return new RoleResponse($role);
    }

    #[Route(
        path: '/store-api/role/{id}',
        name: 'commercial.store-api.role.edit',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_EDIT]],
        methods: ['PATCH'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function edit(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse
    {
        $businessPartnerCustomerId = $businessPartner->getCustomerId();
        $criteria = new Criteria([$id]);
        $criteria->addFilter(new EqualsFilter(
            'businessPartnerCustomerId',
            $businessPartnerCustomerId,
        ));

        $roleSearchResult = $this->roleRepository
            ->search($criteria, $context->getContext());

        if ($roleSearchResult->getTotal() <= 0) {
            throw EmployeeManagementException::roleNotFound();
        }

        $data = [
            'id' => $id,
            'name' => $request->get('name'),
            'businessPartnerCustomerId' => $businessPartnerCustomerId,
            'permissions' => $request->get('permissions'),
        ];

        $setDefaultRole = (bool) $request->get('isDefaultRole');
        $defaultRoleHasChanged = ($businessPartner->getDefaultRoleId() === $id) ^ $setDefaultRole;

        if ($defaultRoleHasChanged) {
            $this->setDefaultRoleId($setDefaultRole ? $id : null, $context, $businessPartner);
        }

        $this->validateRole($context, $data);
        $this->roleRepository->update([$data], $context->getContext());

        /** @var RoleEntity $updatedRole */
        $updatedRole = $this->roleRepository
            ->search($criteria, $context->getContext())->first();

        return new RoleResponse($updatedRole);
    }

    #[Route(
        path: '/store-api/role/{id}',
        name: 'commercial.store-api.role.delete',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_DELETE]],
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

        $isValidRole = $this->roleRepository
            ->searchIds($criteria, $context->getContext())
            ->getTotal() > 0;

        if (!$isValidRole) {
            throw EmployeeManagementException::roleNotFound();
        }

        $this->roleRepository->delete([['id' => $id]], $context->getContext());

        return new NoContentResponse();
    }

    #[Route(
        path: '/store-api/role/default',
        name: 'commercial.store-api.role.default.set',
        defaults: ['_b2bEmployeeCan' => [BaseEmployeePermissions::ROLE_EDIT]],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function setDefault(?string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): NoContentResponse
    {
        $this->setDefaultRoleId($id, $context, $businessPartner);

        return new NoContentResponse();
    }

    protected function setDefaultRoleId(?string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): void
    {
        $data = [
            'id' => $businessPartner->getId(),
            'defaultRoleId' => $id,
        ];

        $this->validateBusinessPartner($context, $data);
        $this->businessPartnerRepository->update([$data], $context->getContext());
    }

    private function getRoleResponse(EntityWrittenContainerEvent $event, Context $context): RoleResponse
    {
        $keys = $event->getPrimaryKeys(RoleDefinition::ENTITY_NAME);

        /** @var RoleEntity $role */
        $role = $this->roleRepository
            ->search(new Criteria($keys), $context)
            ->first();

        return new RoleResponse($role);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validate(DataValidationFactoryInterface $factory, SalesChannelContext $context, array $data): void
    {
        $validation = $factory->create($context);

        $validationEvent = new BuildValidationEvent($validation, new DataBag($data), $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $violations = $this->validator->getViolations($data, $validation);

        if ($violations->count()) {
            throw new ConstraintViolationException($violations, $data);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRole(SalesChannelContext $context, array $data): void
    {
        $this->validate($this->roleValidationFactory, $context, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateBusinessPartner(SalesChannelContext $context, array $data): void
    {
        $this->validate($this->businessPartnerValidationFactory, $context, $data);
    }
}
