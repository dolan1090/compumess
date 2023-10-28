<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\PermissionEventListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\PermissionListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollector;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Permission\PermissionDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationFactoryInterface;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class PermissionRoute extends AbstractPermissionRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $permissionRepository,
        private readonly PermissionEventCollector $permissionEventCollector,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $permissionValidationFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getDecorated(): AbstractPermissionRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/permission',
        name: 'commercial.store-api.permission.list',
        defaults: ['_loginRequired' => true, '_entity' => 'b2b_permission'],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function list(SalesChannelContext $context, Criteria $criteria): PermissionEventListResponse
    {
        return new PermissionEventListResponse(
            $this->permissionEventCollector->collect($context->getContext())
        );
    }

    #[Route(
        path: '/store-api/permission',
        name: 'commercial.store-api.permission.add',
        defaults: ['_loginRequired' => true],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function add(Request $request, SalesChannelContext $context): PermissionListResponse
    {
        /** @var array<string, string> $data */
        $data = [
            'name' => $request->get('name'),
            'group' => $request->get('group'),
            'dependencies' => $request->get('dependencies', []),
        ];

        $this->validate($context, $data);
        $result = $this->permissionRepository->create([$data], $context->getContext());

        return $this->getPermissionResponse($result, $context->getContext());
    }

    /**
     * @param array<string, string> $data
     */
    private function validate(SalesChannelContext $context, array $data): void
    {
        $factory = $this->permissionValidationFactory->create($context);

        $permissions = $this->permissionEventCollector->collect($context->getContext())->filter(function (PermissionEvent $permission) use ($data) {
            return $permission->getPermissionName() === $data['name'];
        });

        if ($permissions->count() > 0) {
            throw EmployeeManagementException::alreadyExistingPermission($data['name']);
        }

        $event = new BuildValidationEvent($factory, new DataBag($data), $context->getContext());
        $this->eventDispatcher->dispatch($event);

        $violations = $this->validator->getViolations($data, $factory);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $data);
        }
    }

    private function getPermissionResponse(EntityWrittenContainerEvent $event, Context $context): PermissionListResponse
    {
        $keys = $event->getPrimaryKeys(PermissionDefinition::ENTITY_NAME);

        $appPermissions = $this->permissionRepository->search(new Criteria($keys), $context);

        return new PermissionListResponse($appPermissions);
    }
}
