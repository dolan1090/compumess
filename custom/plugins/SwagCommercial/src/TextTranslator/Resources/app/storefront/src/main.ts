/**
 * @package inventory
 */

import ReviewTranslator from './plugin/review-translator.plugin';

interface StorefrontWindow extends Window {
    PluginManager: {
        register: (pluginName: string, plugin: any, selector: string) => void;
    };
}

declare var window: StorefrontWindow;

const PluginManager = window.PluginManager;

PluginManager.register('ReviewTranslator', ReviewTranslator, '[data-review-translator]');
