const { Component, Module } = Shopware;

Component.override('sw-flow-sequence-action', () => import('./module/sw-flow/component/sw-flow-sequence-action'));
Component.register('sw-flow-call-webhook-modal', () => import('./module/sw-flow/component/modals/sw-flow-call-webhook-modal'));
Component.register('sw-flow-call-webhook-parameter-grid', () => import('./module/sw-flow/component/sw-flow-call-webhook-parameter-grid'));
Component.override('sw-flow-detail', () => import('./module/sw-flow/component/sw-flow-detail'));
Component.register('sw-flow-call-webhook-log', () => import('./module/sw-flow/component/sw-flow-call-webhook-log'));
Component.register('sw-flow-call-webhook-log-detail-modal', () => import('./module/sw-flow/component/modals/sw-flow-call-webhook-log-detail-modal'));

Module.register('sw-flow-builder', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.flow.detail') {
            currentRoute.children.push({
                component: 'sw-flow-call-webhook-log',
                name: 'sw.flow.detail.log',
                isChildren: true,
                path: '/sw/flow/detail/:id/log',
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
    callWebhook: 'regular-link',
});
Shopware.Service('flowBuilderService').addLabels({
    callWebhook: 'sw-flow-call-webhook.action.titleCallWebhook',
    baseUrl: 'sw-flow-call-webhook.modal.general.labelBaseUrl',
    description: 'sw-flow-call-webhook.modal.general.labelDescription',
    method: 'sw-flow-call-webhook.modal.general.labelMethod'
});

Shopware.Service('flowBuilderService').addActionNames({
    CALL_WEBHOOK: 'action.call.webhook',
});

Shopware.Service('flowBuilderService').addActionGroupMapping({
    'action.call.webhook': 'general',
});
