import './constant/flow-sharing.constant';
import './snippet';

import flowSharingState from './module/sw-flow/state/flowSharing.state';
import FlowSharingService from './core/service/api/flow-sharing.service';

Shopware.Component.override('sw-flow-index', () => import('./module/sw-flow/page/sw-flow-index'));
Shopware.Component.override('sw-flow-detail', () => import('./module/sw-flow/page/sw-flow-detail'));
Shopware.Component.override('sw-flow-list', () => import('./module/sw-flow/view/sw-flow-list'));
Shopware.Component.override('sw-flow-sequence-action', () => import('./module/sw-flow/component/sw-flow-sequence-action'));
Shopware.Component.override('sw-flow-sequence-condition', () => import('./module/sw-flow/component/sw-flow-sequence-condition'));
Shopware.Component.override('sw-flow-change-customer-group-modal', () => import('./module/sw-flow/component/modals/sw-flow-change-customer-group-modal'));
Shopware.Component.override('sw-flow-mail-send-modal', () => import('./module/sw-flow/component/modals/sw-flow-mail-send-modal'));
Shopware.Component.override('sw-flow-tag-modal', () => import('./module/sw-flow/component/modals/sw-flow-tag-modal'));
Shopware.Component.override('sw-flow-set-entity-custom-field-modal', () => import('./module/sw-flow/component/modals/sw-flow-set-entity-custom-field-modal'));
Shopware.Component.register('sw-flow-sequence-error', () => import('./module/sw-flow/component/sw-flow-sequence-error'));
Shopware.Component.register('sw-flow-sequence-modal-error', () => import('./module/sw-flow/component/sw-flow-sequence-modal-error'));
Shopware.Component.register('sw-flow-download-modal', () => import('./module/sw-flow/component/modals/sw-flow-download-modal'));
Shopware.Component.register('sw-flow-upload-modal', () => import('./module/sw-flow/component/modals/sw-flow-upload-modal'));

const { Application, State, Service } = Shopware;

State.registerModule('swFlowSharingState', flowSharingState);

Service().register('flowSharingService', () => {
    const initContainer = Application.getContainer('init');
    return new FlowSharingService(initContainer.httpClient, Service('loginService'));
});
