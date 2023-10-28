<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Page;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[Package('checkout')]
class AccountQuickOrderPageLoader
{
    public function __construct(private readonly GenericPageLoaderInterface $genericLoader)
    {
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext): AccountQuickOrderPage
    {
        $page = $this->genericLoader->load($request, $salesChannelContext);

        return AccountQuickOrderPage::createFrom($page);
    }
}
