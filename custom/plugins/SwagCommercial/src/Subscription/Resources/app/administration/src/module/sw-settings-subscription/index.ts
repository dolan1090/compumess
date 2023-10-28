import { check2, componentRegister, moduleRegister } from '../../helper/license.helper';

// ACL
import './acl';

/**
 * @package checkout
 *
 * @private
 */
componentRegister('sw-settings-subscription-index', () => import('./page/sw-settings-subscription-index'));

// Interval
componentRegister('sw-settings-subscription-interval-detail', () => import('./page/sw-settings-subscription-interval-detail'));
componentRegister('sw-settings-subscription-intervals', () => import('./view/sw-settings-subscription-intervals'));
componentRegister('sw-settings-subscription-interval-advanced-settings-modal', () => import('./component/sw-settings-subscription-interval-advanced-settings-modal'));

// Interval - Preview
componentRegister('sw-settings-subscription-interval-preview-banner', () => import('./component/sw-settings-subscription-interval-preview-banner'));
componentRegister('sw-settings-subscription-interval-preview-modal', () => import('./component/sw-settings-subscription-interval-preview-modal'));

// Plan
componentRegister('sw-settings-subscription-plans', () => import('./view/sw-settings-subscription-plans'));
componentRegister('sw-settings-subscription-plan-detail', () => import('./page/sw-settings-subscription-plan-detail'));
componentRegister('sw-settings-subscription-plan-general', () => import('./view/sw-settings-subscription-plan-general'));
componentRegister('sw-settings-subscription-plan-products', () => import('./view/sw-settings-subscription-plan-products'));
componentRegister('sw-settings-subscription-plan-products-modal', () => import('./component/sw-settings-subscription-plan-products-modal'));

moduleRegister('sw-settings-subscription', {
    type: 'plugin',
    name: 'sw-settings-subscription',
    entity: 'subscription',
    title: 'commercial.subscriptions.settings.subscriptions',
    description: 'commercial.subscriptions.settings.subscriptions',
    version: '1.0.0',
    targetVersion: '1.0.0',
    icon: 'regular-cog',
    color: '#9AA8B5',
    favicon: 'icon-module-settings.png',
    routes: {
        index: {
            component: 'sw-settings-subscription-index',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'plans_and_intervals.viewer',
            },
            redirect: {
                name: 'sw.settings.subscription.index.plans',
            },
            children: {
                plans: {
                    component: 'sw-settings-subscription-plans',
                    path: 'plans',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'plans_and_intervals.viewer',
                    },
                },
                intervals: {
                    component: 'sw-settings-subscription-intervals',
                    path: 'intervals',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'plans_and_intervals.viewer',
                    },
                },
            },
        },
        intervalDetail: {
            component: 'sw-settings-subscription-interval-detail',
            path: 'interval/detail/:id',
            meta: {
                parentPath: 'sw.settings.subscription.index.intervals',
                privilege: 'plans_and_intervals.viewer',
            },
        },
        intervalCreate: {
            component: 'sw-settings-subscription-interval-detail',
            path: 'interval/create',
            meta: {
                parentPath: 'sw.settings.subscription.index.intervals',
                privilege: 'plans_and_intervals.creator',
            },
        },
        planCreate: {
            path: 'plan/create',
            component: 'sw-settings-subscription-plan-detail',
            meta: {
                parentPath: 'sw.settings.subscription.index.plans',
                privilege: 'plans_and_intervals.creator',
            },
            redirect: {
                name: 'sw.settings.subscription.planCreate.general',
            },
            children: {
                general: {
                    component: 'sw-settings-subscription-plan-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.settings.subscription.index.plans',
                        privilege: 'plans_and_intervals.creator',
                    },
                },
                products: {
                    component: 'sw-settings-subscription-plan-products',
                    path: 'products',
                    meta: {
                        parentPath: 'sw.settings.subscription.index.plans',
                        privilege: 'plans_and_intervals.creator',
                    },
                },
            },
        },
        planDetail: {
            path: 'plan/detail/:id',
            component: 'sw-settings-subscription-plan-detail',
            meta: {
                parentPath: 'sw.settings.subscription.index.plans',
                privilege: 'plans_and_intervals.viewer',
            },
            redirect: {
                name: 'sw.settings.subscription.planDetail.general',
            },
            children: {
                general: {
                    component: 'sw-settings-subscription-plan-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.settings.subscription.index.plans',
                        privilege: 'plans_and_intervals.viewer',
                    },
                },
                products: {
                    component: 'sw-settings-subscription-plan-products',
                    path: 'products',
                    meta: {
                        parentPath: 'sw.settings.subscription.index.plans',
                        privilege: 'plans_and_intervals.viewer',
                    },
                },
            },
        },
    },
    settingsItem: {
        group: 'shop',
        to: 'sw.settings.subscription.index',
        icon: 'regular-sync',
    },
});

check2();
