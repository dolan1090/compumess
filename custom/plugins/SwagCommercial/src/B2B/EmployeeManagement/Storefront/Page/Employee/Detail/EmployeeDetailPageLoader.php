<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\Detail;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractEmployeeRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractRoleRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class EmployeeDetailPageLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractEmployeeRoute $employeeRoute,
        private readonly AbstractRoleRoute $roleRoute,
    ) {
    }

    public function loadCreate(Request $request, SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): EmployeeDetailPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = EmployeeDetailPage::createFrom($page);

        /** @var RoleCollection $collection */
        $collection = $this->roleRoute->list($request, $salesChannelContext, $businessPartner)
            ->getRoles()->getEntities();

        $page->setAvailableRoles($collection);
        $page->setDefaultRoleId($businessPartner->getDefaultRoleId());

        $this->eventDispatcher->dispatch(new EmployeeDetailPageLoadedEvent($page, $salesChannelContext, $request));

        return $page;
    }

    public function load(string $id, Request $request, SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): EmployeeDetailPage
    {
        $employee = $this->employeeRoute->get($id, $salesChannelContext, $businessPartner)->getEmployee();

        $page = $this->loadCreate($request, $salesChannelContext, $businessPartner);
        $page->setEmployee($employee);

        return $page;
    }
}
