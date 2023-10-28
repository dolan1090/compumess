<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\PermissionEventListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\PermissionListResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractPermissionRoute
{
    abstract public function getDecorated(): AbstractPermissionRoute;

    abstract public function list(SalesChannelContext $context, Criteria $criteria): PermissionEventListResponse;

    abstract public function add(Request $request, SalesChannelContext $context): PermissionListResponse;
}
