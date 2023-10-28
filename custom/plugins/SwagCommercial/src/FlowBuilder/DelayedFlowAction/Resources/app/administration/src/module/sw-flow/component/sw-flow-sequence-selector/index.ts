import template from './sw-flow-sequence-selector.html.twig';
import './sw-flow-sequence-selector.scss';

const { Component, State } = Shopware;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        addDelayAction(): void {
            State.commit('swFlowState/updateSequence', {
                id: this.sequence.id,
                actionName: 'action.delay',
            });
        },
    },
});
