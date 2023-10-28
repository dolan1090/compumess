import template from './sw-flow-sequence.html.twig';

const { Component } = Shopware;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    inject: ['flowBuilderService'],

    template,

    computed: {
        delayConstant() {
            return this.flowBuilderService.getActionName('DELAY');
        },

        isDelayAction(): boolean {
            return this.sequenceData.actionName === this.delayConstant;
        },

        isActionSequence(): boolean {
            return !this.isSelectorSequence
                && !this.isConditionSequence
                && this.sequenceData.actionName !== this.delayConstant;
        },
    },
});
