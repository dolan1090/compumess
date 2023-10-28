<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\DependencyInjection;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

#[Package('buyers-experience')]
class AdvancedSearchExtension extends Extension
{
    /**
     * @param array<array<string, array<string, mixed>|bool|string|int|float|\UnitEnum|null>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $this->addConfig($container, $this->getAlias(), $config);
    }

    /**
     * @param array<array<string, array<string, mixed>|bool|string|int|float|\UnitEnum|null>> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * @param array<string, array<string, mixed>|bool|float|int|string|\UnitEnum|null> $options
     */
    private function addConfig(ContainerBuilder $container, string $alias, array $options): void
    {
        foreach ($options as $key => $option) {
            $container->setParameter($alias . '.' . $key, $option);

            if (\is_array($option)) {
                /** @var array<string, array<string, mixed>|bool|float|int|string|\UnitEnum|null> $option */
                $this->addConfig($container, $alias . '.' . $key, $option);
            }
        }
    }
}
