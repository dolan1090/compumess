/**
 * @package buyers-experience
 */
import type { Route } from 'vue-router';
import type { ModuleManifest } from '@administration/src/core/factory/module.factory';
import { LICENSE_ADMIN_UI, ES_MULTILINGUAL_INDEX } from '../../constants';

const { Component, License, Feature, Module } = Shopware;

interface CurrentRoute extends Route {
    children: {
        isChildren: boolean,
        component: string,
        name: string,
        path: string,
        meta: {
            parentPath: string,
            privilege: string,
        },
    }[],
}

const swagAdvancedSearch: ModuleManifest = {
    type: 'plugin',
    name: 'swag-advanced-search',
    entity: 'advanced_search_config',
    routes: {},
    title: '',

    routeMiddleware(next: () => void, currentRoute: CurrentRoute): void {
        if (currentRoute.name.includes('sw.settings.search.index')) {
            currentRoute.children.push({
                isChildren: true,
                component: 'swag-advanced-search-boosting',
                name: 'sw.settings.search.index.boosting',
                path: '/sw/settings/search/index/boosting',
                meta: {
                    parentPath: 'sw.settings.index',
                    privilege: 'advanced_search_config.viewer',
                },
            });
        }

        next();
    },
};

/* eslint-disable max-len */
if (License.get(LICENSE_ADMIN_UI) && Feature.isActive(ES_MULTILINGUAL_INDEX)) {
    Component.register('swag-advanced-search-boosting', () => import('./view/swag-advanced-search-boosting'));
    Component.register('swag-advanced-search-boosting-modal', () => import('./component/swag-advanced-search-boosting-modal'));
    Component.register('swag-advanced-search-hit-count', () => import('./component/swag-advanced-search-hit-count'));
    Component.register('swag-advanced-search-entity-stream-field-select', () => import('./component/swag-advanced-search-entity-stream-field-select'));
    Component.register('swag-advanced-search-entity-stream-value', () => import('./component/swag-advanced-search-entity-stream-value'));

    Component.extend('swag-advanced-search-entity-stream', 'sw-condition-tree', () => import('./component/swag-advanced-search-entity-stream'));
    Component.extend('swag-advanced-search-entity-stream-filter', 'sw-condition-base', () => import('./component/swag-advanced-search-entity-stream-filter'));

    Module.register('swag-advanced-search', swagAdvancedSearch);
}
/* eslint-enable max-len */
