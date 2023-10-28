/**
 * @package buyers-experience
 */
import swAdvancedSearchState from './state/sw-advanced-search.state';
import { LICENSE_ADMIN_UI, ES_MULTILINGUAL_INDEX } from '../../constants';

const { Component, License, Feature, State } = Shopware;

/* eslint-disable max-len */
if (License.get(LICENSE_ADMIN_UI) && Feature.isActive(ES_MULTILINGUAL_INDEX)) {
    Component.override('sw-settings-search', () => import('./page/sw-settings-search'));
    Component.override('sw-settings-search-view-general', () => import('./view/sw-settings-search-view-general'));
    Component.override('sw-settings-search-view-live-search', () => import('./view/sw-settings-search-view-live-search'));
    Component.override('sw-settings-search-search-behaviour', () => import('./component/sw-settings-search-search-behaviour'));
    Component.override('sw-settings-search-searchable-content', () => import('./component/sw-settings-search-searchable-content'));
    Component.override('sw-settings-search-searchable-content-general', () => import('./component/sw-settings-search-searchable-content-general'));
    Component.override('sw-settings-search-searchable-content-customfields', () => import('./component/sw-settings-search-searchable-content-customfields'));
    Component.override('sw-settings-search-live-search', () => import('./component/sw-settings-search-live-search'));

    State.registerModule('swAdvancedSearchState', swAdvancedSearchState);
}
/* eslint-enable max-len */
