import './acl';
import { check2, componentRegister, moduleRegister } from '../../helper/license.helper';

/**
 * @package checkout
 *
 * @private
 */
// Subscription list
componentRegister('sw-subscription-list', () => import('./page/sw-subscription-list'));

// Subscription detail
componentRegister('sw-subscription-detail', () => import('./page/sw-subscription-detail'));
componentRegister('sw-subscription-detail-details', () => import('./view/sw-subscription-detail-details'));
componentRegister('sw-subscription-detail-orders', () => import('./view/sw-subscription-detail-orders'));

// Subscription detail general
componentRegister('sw-subscription-detail-general-info', () => import('./component/sw-subscription-detail-general-info'));
componentRegister('sw-subscription-detail-general-items', () => import('./component/sw-subscription-detail-general-items'));
componentRegister('sw-subscription-detail-general', () => import('./view/sw-subscription-detail-general'));

moduleRegister('sw-subscription', {
    type: 'plugin',
    name: 'sw-subscription',
    entity: 'subscription',
    title: 'commercial.subscriptions.subscriptions.general.mainMenuItem',
    description: 'commercial.subscriptions.subscriptions.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#A092F0',
    icon: 'regular-shopping-bag',
    favicon: 'icon-module-orders.png',

    routes: {
        index: {
            components: {
                default: 'sw-subscription-list',
            },
            path: 'index',
            meta: {
                privilege: 'subscription.viewer',
            },
        },

        detail: {
            path: 'detail/:id',
            component: 'sw-subscription-detail',
            meta: {
                parentPath: 'sw.subscription.index',
                privilege: 'subscription.viewer',
            },
            redirect: {
                name: 'sw.subscription.detail.general',
            },
            children: {
                general: {
                    component: 'sw-subscription-detail-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.subscription.index',
                        privilege: 'subscription.viewer',
                    },
                },
                details: {
                    component: 'sw-subscription-detail-details',
                    path: 'details',
                    meta: {
                        parentPath: 'sw.subscription.index',
                        privilege: 'subscription.viewer',
                    },
                },
                orders: {
                    component: 'sw-subscription-detail-orders',
                    path: 'orders',
                    meta: {
                        parentPath: 'sw.subscription.index',
                        privilege: 'subscription.viewer',
                    },
                },
            },
        },
    },

    navigation: [{
        id: 'sw-subscription-list',
        label: 'commercial.subscriptions.subscriptions.general.mainMenuItemLabel',
        color: '#ff3d58',
        path: 'sw.subscription.index',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-order',
        position: 100,
    }],
});

check2();
