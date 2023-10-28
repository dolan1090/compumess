import overwriteDefaultSearchConfiguration from './default-search-configuration';

/**
 * @package checkout
 */
function overwriteDefaultSearch(): void {
    const orderModuleRegistry = Shopware.Module.getModuleByEntityName('order');
    if (!orderModuleRegistry?.manifest?.defaultSearchConfiguration) {
        return;
    }

    orderModuleRegistry.manifest.defaultSearchConfiguration = {
        ...orderModuleRegistry.manifest.defaultSearchConfiguration,
        ...overwriteDefaultSearchConfiguration,
    }
}

overwriteDefaultSearch();
