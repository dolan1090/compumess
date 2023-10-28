<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Common;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ActionSetUpCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $actions = \array_keys($container->findTaggedServiceIds('swag.publisher.storefront-action'));

        foreach ($actions as $serviceId) {
            $actionDefinition = $container->getDefinition($serviceId);

            if (!$class = $actionDefinition->getClass()) {
                continue;
            }

            if (\method_exists($class, 'setTwig')) {
                $actionDefinition
                    ->addMethodCall(
                        'setTwig',
                        [new Reference('twig')]
                    );
            }

            if (\method_exists($class, 'setContainer')) {
                $actionDefinition
                    ->addMethodCall(
                        'setContainer',
                        [new Reference('service_container')]
                    );
            }
        }
    }
}
