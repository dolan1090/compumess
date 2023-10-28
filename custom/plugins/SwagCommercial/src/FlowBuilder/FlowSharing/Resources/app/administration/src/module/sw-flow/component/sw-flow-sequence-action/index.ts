import template from './sw-flow-sequence-action.html.twig';
import type {Action} from '../../flow.types';

const { State } = Shopware;

/**
 * @package business-ops
 */
export default {
    template,

    methods: {
        editAction(action: Action): void {
            if (!this.currentSequence.error || Object.keys(this.currentSequence.error).length === 0) {
                return this.$super('editAction', action);
            }

            if (!action.name) {
                return;
            }

            State.commit('swFlowState/updateSequence', {
                id: this.currentSequence.id,
                actionName: action.name,
                config: action.config,
                error: {}
            });
        },
    }
};
