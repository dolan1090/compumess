import './page/acris-customer-price-list';
import './page/acris-customer-price-create';
import './page/acris-customer-price-detail';
import './acris-settings-item.scss';

import deDE from "./snippet/de-DE";
import enGB from "./snippet/en-GB";

const { Module } = Shopware;

Module.register('acris-customer-price', {
    type: 'plugin',
    name: 'AcrisPartner',
    title: 'acris-customer-price.general.mainMenuItemGeneral',
    description: 'acris-customer-price.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#a6c836',
    icon: 'regular-user',
    favicon: 'icon-module-settings.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'acris-customer-price-list',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index'
            }
        },
        detail: {
            component: 'acris-customer-price-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'acris.customer.price.index'
            }
        },
        create: {
            component: 'acris-customer-price-create',
            path: 'create',
            meta: {
                parentPath: 'acris.customer.price.index'
            }
        }
    },

    settingsItem: [
        {
            name:   'acris-customer-price-index',
            to:     'acris.customer.price.index',
            label:  'acris-customer-price.general.mainMenuItemGeneral',
            group:  'plugins',
            icon:   'regular-user'
        }
    ]
});
