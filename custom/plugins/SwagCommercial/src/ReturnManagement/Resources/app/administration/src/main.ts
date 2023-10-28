// Core service
import './core/index';
import './modules/swag-return-management/acl';
import './core/modules/sw-order';

import { TOGGLE_KEY } from './config';

// Core components
Shopware.Component.override('sw-order-detail-general',() => import('./core/modules/sw-order/view/sw-order-detail-general'));
Shopware.Component.override('sw-order-list',() => import('./core/modules/sw-order/page/sw-order-list'));
Shopware.Component.override('sw-order-detail',() => import('./core/modules/sw-order/page/sw-order-detail'));
Shopware.Component.override('sw-order-line-items-grid',() => import('./core/modules/sw-order/component/sw-order-line-items-grid'));
Shopware.Component.override('sw-customer-base-info',() => import('./core/modules/sw-customer/component/sw-customer-base-info'));
Shopware.Component.override('sw-order-select-document-type-modal',() => import('./core/modules/sw-order/component/sw-order-select-document-type-modal'));
Shopware.Component.override('sw-order-send-document-modal',() => import('./core/modules/sw-order/component/sw-order-send-document-modal'));
Shopware.Component.override('sw-bulk-edit-order-documents-download-documents',() => import('./core/modules/sw-bulk-edit/component/sw-bulk-edit-order/sw-bulk-edit-order-documents-download-documents'));
Shopware.Component.extend('sw-order-document-settings-partial-cancellation-modal', 'sw-order-document-settings-modal', () => import('./core/modules/sw-order/component/sw-order-document-settings-partial-cancellation-modal'));

// Modules components
Shopware.Component.register('swag-return-management-detail-returns', () => import('./modules/swag-return-management/view/swag-return-management-detail-returns'));
Shopware.Component.register('swag-return-management-create-return-modal', () => import('./modules/swag-return-management/component/swag-return-management-create-return-modal'));
Shopware.Component.register('swag-return-management-return-card', () => import ('./modules/swag-return-management/component/swag-return-management-return-card'));
Shopware.Component.register('swag-return-management-return-line-items-grid', () => import ('./modules/swag-return-management/component/swag-return-management-return-line-items-grid'));
Shopware.Component.register('swag-return-management-set-item-status-modal', () => import ('./modules/swag-return-management/component/swag-return-management-set-item-status-modal'));
Shopware.Component.register('swag-return-management-item-detail-modal', () => import ('./modules/swag-return-management/component/swag-return-management-item-detail-modal'));
Shopware.Component.register('swag-return-management-delete-return-modal', () => import ('./modules/swag-return-management/component/swag-return-management-delete-return-modal'));
Shopware.Component.register('swag-return-management-return-card-state-history', () => import ('./modules/swag-return-management/component/swag-return-management-return-card-state-history'));
Shopware.Component.register('swag-return-management-change-return-state-modal', () => import ('./modules/swag-return-management/component/swag-return-management-change-return-state-modal'));
Shopware.Component.register('swag-return-management-delete-line-item-modal', () => import ('./modules/swag-return-management/component/swag-return-management-delete-line-item-modal'));
Shopware.Component.register('swag-return-management-delete-return-item-modal', () => import ('./modules/swag-return-management/component/swag-return-management-delete-return-item-modal'));

if (Shopware.License.get(TOGGLE_KEY)) {
    Shopware.Module.register('swag-return-management', {
        type: 'plugin',
        routes: {},
        name: '',
        title: '',
        routeMiddleware(next, currentRoute) {
            if (currentRoute.name === 'sw.order.detail') {
                currentRoute.children.push({
                    component: 'swag-return-management-detail-returns',
                    name: 'swag.return.management.order.detail.returns',
                    isChildren: true,
                    path: '/sw/order/detail/:id/returns',
                    meta: {
                        parentPath: 'sw.order.index',
                        privilege: 'order.viewer',
                    },
                });
            }

            next();
        },
    });
}
