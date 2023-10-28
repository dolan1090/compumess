<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\DependencyInjection;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

#[Package('buyers-experience')]
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('advanced_search');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('completion')->performNoDeepMerging()->variablePrototype()->end()->end()
                ->arrayNode('cross_search')->variablePrototype()->end()->end()
                ->arrayNode('analysis')->variablePrototype()->end()->end()
                ->arrayNode('language_analyzer_mapping')->defaultValue([])->scalarPrototype()->end()->end()
                ->arrayNode('cache')
                    ->children()
                        ->arrayNode('invalidation')
                            ->children()
                                ->arrayNode('multi_search_route')
                                    ->performNoDeepMerging()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('multi_suggest_route')
                                    ->performNoDeepMerging()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
