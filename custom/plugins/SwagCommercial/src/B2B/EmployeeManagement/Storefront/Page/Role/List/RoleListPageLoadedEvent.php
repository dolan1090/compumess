<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Role\List;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class RoleListPageLoadedEvent extends PageLoadedEvent
{
    protected RoleListPage $page;

    public function __construct(
        RoleListPage $page,
        SalesChannelContext $salesChannelContext,
        Request $request
    ) {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): RoleListPage
    {
        return $this->page;
    }
}
