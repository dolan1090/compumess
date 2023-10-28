Shopware.Component.override('sw-flow-list', () => import('./module/sw-flow/component/sw-flow-list'));
Shopware.Component.override('sw-flow-detail', () => import('./module/sw-flow/component/sw-flow-detail'));
Shopware.Component.override('sw-flow-detail-flow', () => import('./module/sw-flow/component/sw-flow-detail-flow'));
Shopware.Component.override('sw-flow-sequence', () => import('./module/sw-flow/component/sw-flow-sequence'));
Shopware.Component.override('sw-flow-sequence-selector', () => import('./module/sw-flow/component/sw-flow-sequence-selector'));
Shopware.Component.override('sw-flow-sequence-action', () => import('./module/sw-flow/component/sw-flow-sequence-action'));
Shopware.Component.override('sw-flow-sequence-condition', () => import('./module/sw-flow/component/sw-flow-sequence-condition'));
Shopware.Component.extend('sw-flow-delay-action', 'sw-flow-sequence-action', () => import('./module/sw-flow/component/sw-flow-delay-action'));
Shopware.Component.register('sw-flow-sequence-label', () => import('./module/sw-flow/component/sw-flow-sequence-label'));
Shopware.Component.register('sw-flow-delay-tab', () => import('./module/sw-flow/view/sw-flow-delay-tab'));
Shopware.Component.register('sw-flow-delay-modal', () => import('./module/sw-flow/component/modals/sw-flow-delay-modal'));
Shopware.Component.register('sw-flow-action-detail-modal', () => import('./module/sw-flow/component/modals/sw-flow-action-detail-modal'));
Shopware.Component.register('sw-flow-delay-edit-warning-modal', () => import('./module/sw-flow/component/modals/sw-flow-delay-edit-warning-modal'));
Shopware.Component.override('sw-flow-event-change-confirm-modal', () => import('./module/sw-flow/component/modals/sw-flow-event-change-confirm-modal'));

// Store
import flowState from './state/swFlowDelay.store';

// Service
import './init/api-service.init';

import DelayedFlowAction from './service/sw-flow-delay.service';

const { State, Module } = Shopware;

Shopware.Service().register('delayedFlowActionService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new DelayedFlowAction(initContainer.httpClient, Shopware.Service('loginService'));
});

State.registerModule('swFlowDelay', flowState);

Module.register('sw-flow-delay', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.flow.detail') {
            currentRoute.children.push({
                component: 'sw-flow-delay-tab',
                name: 'sw.flow.detail.delay',
                isChildren: true,
                path: '/sw/flow/detail/:id/delay',
                meta: {
                    parentPath: 'sw.flow.index',
                    privilege: 'flow.viewer',
                },
            });
        }

        next(currentRoute);
    },
});

Shopware.Service('flowBuilderService').addIcons({
    delay: 'regular-hourglass',
});
Shopware.Service('flowBuilderService').addLabels({
    delay: 'sw-flow-delay.detail.sequence.delayActionTitle',
});
Shopware.Service('flowBuilderService').addActionNames({
    DELAY: 'action.delay',
});
