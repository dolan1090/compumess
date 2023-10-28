<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\RoleListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\RoleResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractRoleRoute
{
    abstract public function getDecorated(): AbstractRoleRoute;

    abstract public function create(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse;

    abstract public function list(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleListResponse;

    abstract public function get(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse;

    abstract public function edit(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleResponse;

    abstract public function delete(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): NoContentResponse;

    abstract public function setDefault(?string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): NoContentResponse;
}
