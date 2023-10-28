<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch;

use Shopware\Commercial\AdvancedSearch\DependencyInjection\AdvancedSearchExtension;
use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @internal
 *
 * @phpstan-import-type Feature from CommercialBundle
 */
#[Package('buyers-experience')]
class AdvancedSearch extends CommercialBundle
{
    /**
     * @var string
     */
    protected $name = 'AdvancedSearch';

    /**
     * @return list<Feature>
     */
    public function describeFeatures(): array
    {
        return [
            [
                'code' => 'ADVANCED_SEARCH',
                'name' => 'Advanced search',
                'description' => 'Advanced search',
            ],
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        /** @var array<string, string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        if (!\array_key_exists('Elasticsearch', $bundles)) {
            return;
        }

        parent::build($container);

        $this->buildConfig($container);
    }

    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new AdvancedSearchExtension();
    }

    private function buildConfig(ContainerBuilder $container): void
    {
        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = $this->getPath() . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
    }
}
