<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Annotation;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 */
#[Package('checkout')]
class B2bRouteParamsCleanupListener
{
    public function __invoke(RequestEvent $event): void
    {
        $routeParams = $event->getRequest()->attributes->get('_route_params', []);

        if (\is_array($routeParams)) {
            unset($routeParams[B2bEmployeePermissionValidator::ATTRIBUTE_B2B_EMPLOYEE_PERMISSIONS]);
        }

        $event->getRequest()->attributes->set('_route_params', $routeParams);
    }
}
