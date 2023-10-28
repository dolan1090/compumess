<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Controller;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\EmployeeListResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Response\EmployeeResponse;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractEmployeeRoute
{
    abstract public function getDecorated(): AbstractEmployeeRoute;

    abstract public function create(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;

    abstract public function list(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeListResponse;

    abstract public function get(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;

    abstract public function edit(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;

    abstract public function activate(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;

    abstract public function deactivate(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;

    abstract public function delete(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): NoContentResponse;

    abstract public function reinvite(string $id, Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EmployeeResponse;
}
