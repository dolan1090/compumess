<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\RouteAccess;

use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
abstract class AbstractEmployeeRouteAccessLoader
{
    abstract public function getDecorated(): AbstractEmployeeRouteAccessLoader;

    /**
     * @return array{allowed: string[], denied: string[]}
     */
    abstract public function load(): array;
}
