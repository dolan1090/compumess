<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\List;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractRoleRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class RoleListPageLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractRoleRoute $roleRoute
    ) {
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): RoleListPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = RoleListPage::createFrom($page);
        $page->setRoles(StorefrontSearchResult::createFrom($this->getRoles($request, $salesChannelContext, $businessPartner)));
        $page->setDefaultRoleId($businessPartner->getDefaultRoleId());

        $this->eventDispatcher->dispatch(new RoleListPageLoadedEvent($page, $salesChannelContext, $request));

        return $page;
    }

    private function getRoles(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EntitySearchResult
    {
        return $this->roleRoute->list($request, $context, $businessPartner)->getRoles();
    }
}
