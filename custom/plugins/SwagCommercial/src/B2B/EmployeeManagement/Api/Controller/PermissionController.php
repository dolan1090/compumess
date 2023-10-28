<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollector;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['api']])]
class PermissionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly PermissionEventCollector $permissionEventCollector,
    ) {
    }

    #[Route(
        path: '/api/_action/permission',
        name: 'commercial.api.permission.list',
        methods: ['GET'],
        condition: 'service(\'license\').check(\'EMPLOYEE_MANAGEMENT-4838834\')'
    )]
    public function list(Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->permissionEventCollector->collect($context)
        );
    }
}
