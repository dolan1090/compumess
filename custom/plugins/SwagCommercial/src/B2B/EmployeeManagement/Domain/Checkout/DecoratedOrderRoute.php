<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Checkout;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Login\SalesChannelContextRestoredSubscriber;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\BaseEmployeePermissions;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\OrderRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class DecoratedOrderRoute extends AbstractOrderRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractOrderRoute $decorated)
    {
    }

    public function getDecorated(): AbstractOrderRoute
    {
        return $this->decorated;
    }

    #[Route(
        path: '/store-api/order',
        name: 'store-api.order',
        defaults: ['_entity' => 'order'],
        methods: ['GET', 'POST']
    )]
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): OrderRouteResponse
    {
        $employee = $context->getCustomer()?->getExtension(SalesChannelContextRestoredSubscriber::CUSTOMER_EMPLOYEE_EXTENSION);

        if ($employee instanceof EmployeeEntity && !$employee->getRole()?->can(BaseEmployeePermissions::ORDER_READ_ALL)) {
            $criteria->addFilter(new EqualsFilter('orderEmployee.employeeId', $employee->getId()));
        }

        return $this->decorated->load($request, $context, $criteria);
    }
}
