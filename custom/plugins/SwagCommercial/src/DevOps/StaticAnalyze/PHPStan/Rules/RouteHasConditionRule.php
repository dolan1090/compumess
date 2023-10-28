<?php declare(strict_types=1);

namespace Shopware\Commercial\DevOps\StaticAnalyze\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Login\DecoratedLoginRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Login\EmployeeConfirmPasswordRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery\DecoratedSendPasswordRecoveryMailRoute;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery\EmployeeRecoveryIsExpiredRoute;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @implements Rule<InClassNode>
 *
 * @internal
 */
#[Package('core')]
class RouteHasConditionRule implements Rule
{
    /**
     * @var list<string>
     */
    private array $routesWithoutLicenseRule = [
        EmployeeRecoveryIsExpiredRoute::class,
        DecoratedLoginRoute::class,
        EmployeeConfirmPasswordRoute::class,
        DecoratedSendPasswordRecoveryMailRoute::class,
    ];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflect = $node->getClassReflection()->getNativeReflection();

        if (empty($classReflect->getAttributes(Route::class))
            || \in_array($scope->getClassReflection()?->getName(), $this->routesWithoutLicenseRule, true)) {
            return [];
        }

        $methods = $classReflect->getMethods();

        foreach ($methods as $method) {
            $routes = $method->getAttributes(Route::class);
            if (\count($routes) === 0) {
                continue;
            }

            if (!isset($routes[0]->getArgumentsExpressions()['condition'])
                || !\str_contains($routes[0]->getArgumentsExpressions()['condition']->getAttributes()['rawValue'], 'service(\'license\')')) {
                return ['The commercial #Route ' . $routes[0]->getArgumentsExpressions()['path']->getAttributes()['rawValue'] . ' must be attributed `condition: service(\'license\').check(\'toggle\')` for check license.'];
            }
        }

        return [];
    }
}
