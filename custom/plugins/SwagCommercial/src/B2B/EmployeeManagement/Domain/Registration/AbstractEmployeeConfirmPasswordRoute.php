<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SuccessResponse;

#[Package('checkout')]
abstract class AbstractEmployeeConfirmPasswordRoute
{
    abstract public function getDecorated(): AbstractEmployeeConfirmPasswordRoute;

    abstract public function confirmPassword(RequestDataBag $data, SalesChannelContext $context): SuccessResponse;
}
