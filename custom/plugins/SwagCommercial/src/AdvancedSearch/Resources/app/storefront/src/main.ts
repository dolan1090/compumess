/**
 * @package buyers-experience
 */
import AdvancedSearchWidgetPlugin from './plugin/header/advanced-search-widget.plugin';
import Feature from 'src/helper/feature.helper';

declare var window: StorefrontWindow;

const PluginManager = window.PluginManager;

if (Feature.isActive('ES_MULTILINGUAL_INDEX')) {
    /** @deprecated tag:v6.6.0 - Registering plugin on selector "data-search-form" is deprecated. Use "data-search-widget" instead */
    if (Feature.isActive('v6.6.0.0')) {
        PluginManager.register('AdvancedSearchWidgetPlugin', AdvancedSearchWidgetPlugin, '[data-search-widget]');
    } else {
        PluginManager.register('AdvancedSearchWidgetPlugin', AdvancedSearchWidgetPlugin, '[data-search-form]');
    }
}

if (module.hot) {
    module.hot.accept();
}
