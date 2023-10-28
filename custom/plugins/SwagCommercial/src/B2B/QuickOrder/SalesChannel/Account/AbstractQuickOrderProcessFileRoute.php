<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractQuickOrderProcessFileRoute
{
    abstract public function getDecorated(): AbstractQuickOrderProcessFileRoute;

    abstract public function load(Request $request, SalesChannelContext $context): JsonResponse;
}
