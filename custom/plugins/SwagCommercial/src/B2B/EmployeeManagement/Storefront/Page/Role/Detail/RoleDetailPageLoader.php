<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\Detail;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractPermissionRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractRoleRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class RoleDetailPageLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractRoleRoute $roleRoute,
        private readonly AbstractPermissionRoute $permissionRoute,
    ) {
    }

    public function load(?string $id, Request $request, SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): RoleDetailPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = RoleDetailPage::createFrom($page);
        if ($id) {
            $page->setRole($this->getRole($id, $salesChannelContext, $businessPartner));
        }

        $page->setDefaultRoleId($businessPartner->getDefaultRoleId());
        $page->setPermissions($this->permissionRoute->list($salesChannelContext, new Criteria())->getPermissions());

        $this->eventDispatcher->dispatch(new RoleDetailPageLoadedEvent($page, $salesChannelContext, $request));

        return $page;
    }

    private function getRole(string $id, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): RoleEntity
    {
        return $this->roleRoute->get($id, $context, $businessPartner)->getRole();
    }
}
