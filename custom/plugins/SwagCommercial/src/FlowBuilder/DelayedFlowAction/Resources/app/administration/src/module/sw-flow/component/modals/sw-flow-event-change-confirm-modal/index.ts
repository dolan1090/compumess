// @ts-ignore
import template from './sw-flow-event-change-confirm-modal.html.twig';

const { Component } = Shopware;
const { mapState, mapGetters } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    computed: {
        ...mapState('swFlowState', ['flow']),
        ...mapGetters('swFlowState', ['sequences']),


        hasDelayedActions(): boolean {
            return this.sequences.some(item => item.actionName === 'action.delay');
        },

        isShowConfirmOverride(): boolean {
            return !this.flow.active && this.hasDelayedActions;
        },
    },

});
