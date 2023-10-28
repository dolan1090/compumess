<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class AccountSubscriptionPageLoadedEvent extends PageLoadedEvent
{
    protected AccountSubscriptionPage $page;

    public function __construct(
        AccountSubscriptionPage $page,
        SalesChannelContext $salesChannelContext,
        Request $request
    ) {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): AccountSubscriptionPage
    {
        return $this->page;
    }
}
