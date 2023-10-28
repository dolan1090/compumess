<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\RouteAccess;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('checkout')]
class EmployeeRouteAccessLoader extends AbstractEmployeeRouteAccessLoader
{
    private const CONFIG = __DIR__ . '/../../Resources/config/employee_route_access.xml';

    public function getDecorated(): AbstractEmployeeRouteAccessLoader
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @return array{allowed: string[], denied: string[]}
     */
    public function load(): array
    {
        /** @var array{allowed: string[], denied: string[]} $routes */
        $routes = (array) @simplexml_load_file(self::CONFIG);

        return $routes;
    }
}
