/**
 * @package checkout
 */

import './service/index';
import { TOGGLE_KEY } from './config';

Shopware.Component.register('swag-customer-classification-index',() => import('./modules/swag-customer-classification/page/swag-customer-classification-index'));
Shopware.Component.register('swag-customer-classification-basic',() => import('./modules/swag-customer-classification/view/swag-customer-classification-basic'));
Shopware.Component.register('swag-customer-classification-save-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-save-modal'));
Shopware.Component.register('swag-customer-classification-confirm-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-confirm-modal'));
Shopware.Component.register('swag-customer-classification-process-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-process-modal'));
Shopware.Component.register('swag-customer-classification-success-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-success-modal'));
Shopware.Component.register('swag-customer-classification-error-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-error-modal'));
Shopware.Component.register('swag-customer-classification-edit-tag-modal',() => import('./modules/swag-customer-classification/component/swag-customer-classification-edit-tag-modal'));

Shopware.Component.override('sw-customer-list',() => import('./core/sw-customer/page/sw-customer-list'));

if (Shopware.License.get(TOGGLE_KEY)) {
    Shopware.Module.register('swag-customer-classification', {
        type: 'plugin',
        name: 'customer-classification',
        title: 'swag-customer-classification.mainMenuItemIndex',
        color: '#F88962',
        icon: 'regular-users',
        routes: {
            index: {
                component: 'swag-customer-classification-index',
                path: 'index',
                meta: {
                    privilege: 'customer.viewer',
                    parentPath: 'sw.customer.index',
                },
                children: {
                    save: {
                        component: 'swag-customer-classification-save-modal',
                        path: 'save',
                        redirect: {
                            name: 'swag.customer.classification.index.save.confirm',
                        },
                        children: {
                            confirm: {
                                component: 'swag-customer-classification-confirm-modal',
                                path: 'confirm',
                            },
                            process: {
                                component: 'swag-customer-classification-process-modal',
                                path: 'process',
                            },
                            success: {
                                component: 'swag-customer-classification-success-modal',
                                path: 'success',
                            },
                            error: {
                                component: 'swag-customer-classification-error-modal',
                                path: 'error',
                            },
                        },
                    },
                },
            },
        },
    });
}
