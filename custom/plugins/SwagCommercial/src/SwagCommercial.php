<?php declare(strict_types=1);

namespace Shopware\Commercial;

use Shopware\Commercial\AdvancedSearch\AdvancedSearch;
use Shopware\Commercial\AdvancedSearch\AdvancedSearchUninstallHandler;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagement;
use Shopware\Commercial\B2B\EmployeeManagement\EmployeeManagementUninstallHandler;
use Shopware\Commercial\B2B\QuickOrder\QuickOrder;
use Shopware\Commercial\Captcha\Captcha;
use Shopware\Commercial\Captcha\CaptchaUninstallHandler;
use Shopware\Commercial\CheckoutSweetener\CheckoutSweetener;
use Shopware\Commercial\ClassificationCustomer\ClassificationCustomer;
use Shopware\Commercial\ContentGenerator\ContentGenerator;
use Shopware\Commercial\CustomPricing\CustomPricing;
use Shopware\Commercial\ExportAssistant\ExportAssistant;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\DelayedFlowAction;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\DelayedFlowActionActivateHandler;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\DelayedFlowActionUninstallHandler;
use Shopware\Commercial\FlowBuilder\FlowSharing\FlowSharing;
use Shopware\Commercial\FlowBuilder\WebhookFlowAction\WebhookFlowAction;
use Shopware\Commercial\FlowBuilder\WebhookFlowAction\WebhookFlowActionUninstallHandler;
use Shopware\Commercial\ImageClassification\ImageClassification;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\Features;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Licensing\LicenseUpdater;
use Shopware\Commercial\MultiWarehouse\MultiWarehouse;
use Shopware\Commercial\MultiWarehouse\MultiWarehouseUninstallHandler;
use Shopware\Commercial\PropertyExtractor\PropertyExtractor;
use Shopware\Commercial\ReturnManagement\ReturnManagement;
use Shopware\Commercial\ReturnManagement\ReturnManagementUninstallHandler;
use Shopware\Commercial\ReviewSummary\ReviewSummary;
use Shopware\Commercial\RuleBuilderPreview\RuleBuilderPreview;
use Shopware\Commercial\Subscription\Subscription;
use Shopware\Commercial\Subscription\SubscriptionUninstallHandler;
use Shopware\Commercial\System\ActivateHandler;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Commercial\Tests\Integration\Licensing\Api\AdminControllerTest;
use Shopware\Commercial\TextGenerator\TextGenerator;
use Shopware\Commercial\TextTranslator\TextTranslator;
use Shopware\Commercial\TextTranslator\TextTranslatorUninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Store\Authentication\StoreRequestOptionsProvider;
use Shopware\Core\Kernel;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

#[Package('core')]
class SwagCommercial extends Plugin
{
    public function boot(): void
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        $systemConfigService = $this->container->get(SystemConfigService::class);
        $features = $this->container->get(Features::class);

        try {
            License::set($systemConfigService, $features);
        } catch (LicenseExpiredException) {
            // The given license key is expired. Renew now
            $this->container->get(LicenseUpdater::class)->sync();

            License::set($systemConfigService, $features);
        }
    }

    public function install(InstallContext $installContext): void
    {
        try {
            $this->getLicenseUpdater()->sync();
        } catch (\Throwable) {
            // installation has to work always
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        foreach ($this->getUninstallHandlers() as $handler) {
            $handler->uninstall($this->container, $uninstallContext);
        }
    }

    public function update(UpdateContext $updateContext): void
    {
        try {
            $this->getLicenseUpdater()->sync();
        } catch (\Throwable) {
            // update has to work always
        }
    }

    public function activate(ActivateContext $activateContext): void
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        foreach ($this->getActivateHandlers() as $handler) {
            $handler->activate($this->container, $activateContext);
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $fileLocator = new FileLocator($this->getPath());
        $loaderResolver = new LoaderResolver([
            new XmlFileLoader($container, $fileLocator),
            new YamlFileLoader($container, $fileLocator),
            new GlobFileLoader($container, $fileLocator),
            new PhpFileLoader($container, $fileLocator),
        ]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);
        $delegatingLoader->load(__DIR__ . '/Licensing/DependencyInjection/services.xml');

        $delegatingLoader->load(__DIR__ . '/Resources/config/{packages}/*.yaml', 'glob');

        $features = [];
        foreach ($this->getCommercialBundles() as $bundle) {
            $features = [...$features, ...$bundle->describeFeatures()];
        }

        $container->setParameter('swag.commercial.features', $features);
    }

    /**
     * @codeCoverageIgnore
     *
     * This method is complicated to test with mocks as @see RoutingConfigurator::collect is final. An integration
     * test is added with @see AdminControllerTest::testGetAvailableFeatures to assert that the routes are
     * correctly added.
     */
    public function configureRoutes(RoutingConfigurator $routes, string $environment): void
    {
        $confDir = __DIR__ . '/Licensing/Resources/config';

        $routes->import($confDir . '/{routes}/*' . Kernel::CONFIG_EXTS, 'glob');
        $routes->import($confDir . '/{routes}/' . $environment . '/**/*' . Kernel::CONFIG_EXTS, 'glob');
        $routes->import($confDir . '/{routes}' . Kernel::CONFIG_EXTS, 'glob');
        $routes->import($confDir . '/{routes}_' . $environment . Kernel::CONFIG_EXTS, 'glob');

        parent::configureRoutes($routes, $environment);
    }

    /**
     * @return array<CommercialBundle>
     */
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return $this->getCommercialBundles();
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function enrichPrivileges(): array
    {
        $privileges = [];

        foreach ($this->getCommercialBundles() as $bundle) {
            $privileges = array_replace_recursive($privileges, $bundle->enrichPrivileges());
        }

        return $privileges;
    }

    /**
     * @return array<UninstallHandler>
     */
    protected function getUninstallHandlers(): array
    {
        return [
            new AdvancedSearchUninstallHandler(),
            new CaptchaUninstallHandler(),
            new DelayedFlowActionUninstallHandler(),
            new MultiWarehouseUninstallHandler(),
            new ReturnManagementUninstallHandler(),
            new WebhookFlowActionUninstallHandler(),
            new TextTranslatorUninstallHandler(),
            new SubscriptionUninstallHandler(),
            new EmployeeManagementUninstallHandler(),
        ];
    }

    /**
     * @return array<ActivateHandler>
     */
    protected function getActivateHandlers(): array
    {
        return [
            new DelayedFlowActionActivateHandler(),
        ];
    }

    /**
     * @return array<CommercialBundle>
     */
    private function getCommercialBundles(): array
    {
        return [
            new CustomPricing(),
            new Subscription(),
            new RuleBuilderPreview(),
            new DelayedFlowAction(),
            new FlowSharing(),
            new MultiWarehouse(),
            new ReturnManagement(),
            new PropertyExtractor(),
            new TextGenerator(),
            new ClassificationCustomer(),
            new CheckoutSweetener(),
            new WebhookFlowAction(),
            new ReviewSummary(),
            new ContentGenerator(),
            new TextTranslator(),
            new EmployeeManagement(),
            new ExportAssistant(),
            new ImageClassification(),
            new QuickOrder(),
            new AdvancedSearch(),
            new Captcha(),
        ];
    }

    private function getLicenseUpdater(): LicenseUpdater
    {
        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see `src/Core/Kernel.php:186`.');

        if ($this->container->has(LicenseUpdater::class)) {
            return $this->container->get(LicenseUpdater::class);
        }

        return new LicenseUpdater(
            $this->container->get('shopware.store_client'),
            $this->container->get(StoreRequestOptionsProvider::class),
            $this->container->get(SystemConfigService::class)
        );
    }
}
