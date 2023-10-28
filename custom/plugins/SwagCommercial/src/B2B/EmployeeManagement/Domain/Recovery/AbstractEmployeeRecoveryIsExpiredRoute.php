<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
abstract class AbstractEmployeeRecoveryIsExpiredRoute
{
    abstract public function getDecorated(): AbstractEmployeeRecoveryIsExpiredRoute;

    abstract public function load(RequestDataBag $data, SalesChannelContext $context): IsEmployeeRecoveryExpiredResponse;
}
