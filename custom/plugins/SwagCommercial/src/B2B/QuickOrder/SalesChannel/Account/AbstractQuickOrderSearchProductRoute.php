<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account;

use Shopware\Core\Content\Product\SalesChannel\ProductListResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractQuickOrderSearchProductRoute
{
    abstract public function getDecorated(): AbstractQuickOrderSearchProductRoute;

    abstract public function suggest(SalesChannelContext $context, Request $request, Criteria $criteria): ProductListResponse;
}
