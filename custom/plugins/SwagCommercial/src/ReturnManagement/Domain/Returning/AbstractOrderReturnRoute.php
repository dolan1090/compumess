<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\StoreApiResponse;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractOrderReturnRoute
{
    abstract public function return(string $orderId, Request $request, SalesChannelContext $salesChannelContext): ?StoreApiResponse;

    abstract public function getDecorated(): self;
}
