/**
 * @package system-settings
 */
import './core';
import './app';

import SwagExportAssistant from './modules/swag-export-assistant';
import SwagSearchAssistant from './modules/swag-search-assistant';

if (Shopware.License.get('EXPORT_ASSISTANT-2007020')) {
    /* eslint-disable */
    Shopware.Component.override('sw-search-bar',                () => import('./app/components/sw-search-bar'));
    Shopware.Component.override('sw-import-export-view-export', () => import('./modules/swag-export-assistant/views/sw-import-export-view-export'));
    Shopware.Component.register('swag-export-assistant-base',   () => import('./modules/swag-export-assistant/components/swag-export-assistant-base'));
    Shopware.Component.register('swag-export-assistant-modal',  () => import('./modules/swag-export-assistant/components/swag-export-assistant-modal'));
    /* eslint-enable */

    Shopware.Module.register('swag-export-assistant', SwagExportAssistant);
    Shopware.Module.register('swag-search-assistant', SwagSearchAssistant);
}
