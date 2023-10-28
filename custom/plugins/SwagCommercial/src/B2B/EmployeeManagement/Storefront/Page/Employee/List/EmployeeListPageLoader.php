<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\List;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Controller\AbstractEmployeeRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class EmployeeListPageLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractEmployeeRoute $employeeRoute
    ) {
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): EmployeeListPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);
        $page = EmployeeListPage::createFrom($page);
        $page->setEmployees(StorefrontSearchResult::createFrom($this->getEmployees($request, $salesChannelContext, $businessPartner)));

        $this->eventDispatcher->dispatch(new EmployeeListPageLoadedEvent($page, $salesChannelContext, $request));

        return $page;
    }

    private function getEmployees(Request $request, SalesChannelContext $context, BusinessPartnerEntity $businessPartner): EntitySearchResult
    {
        $responseStruct = $this->employeeRoute->list($request, $context, $businessPartner);

        return $responseStruct->getEmployees();
    }
}
