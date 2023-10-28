import template from './sw-flow-detail-flow.html.twig';
import {SEQUENCE_TYPES} from '../../../../constant/sw-flow-delay.constant';

const { Component, State } = Shopware;
const { mapState } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    computed: {
        ...mapState('swFlowDelay', ['showWarningModal']),

        enableWarningModal(): boolean {
            const { type, enabled, actionType } = this.showWarningModal;
            if (actionType === 'DELETE' && type === SEQUENCE_TYPES.DELAY_ACTION) {
                return enabled && localStorage.getItem('delay_deleted') !== 'true'
            }

            return enabled && localStorage.getItem(type) !== 'true'
        },
    },

    methods: {
        onCloseEditModal(): void {
            State.commit('swFlowDelay/setShowWarningModal', { ...this.showWarningModal, name: this.showWarningModal.type, enabled: false });
        },

        onCancelEditModal(): void {
            State.commit('swFlowDelay/setShowWarningModal', { type: '', name: '', enabled: false, id: '' });
        },
    },
});
