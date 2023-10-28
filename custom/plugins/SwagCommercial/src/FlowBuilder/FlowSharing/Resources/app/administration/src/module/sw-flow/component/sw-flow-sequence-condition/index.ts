import template from './sw-flow-sequence-condition.html.twig';
import type {RuleEntity} from '../../flow.types';

const { State } = Shopware;

/**
 * @package business-ops
 */
export default {
    template,

    computed: {
        hasMissingRule() {
            return this.sequence.error && this.sequence.error.type === 'missing-rule';
        },
    },

    methods: {
        onRuleChange(rule: RuleEntity): void {
            if (!this.sequence.error || Object.keys(this.sequence.error).length === 0) {
                return this.$super('onRuleChange', rule);
            }

            if (!rule) {
                return;
            }

            State.commit('swFlowState/updateSequence', {
                id: this.sequence.id,
                error: {},
                rule,
                ruleId: rule.id,
            });

            if (this.selectedRuleId) {
                // Update other conditions which use the same rule
                this.sequences.forEach(sequence => {
                    if (sequence.ruleId !== this.selectedRuleId
                        || sequence.id === this.sequence.id) {
                        return;
                    }

                    State.commit('swFlowState/updateSequence', {
                        id: sequence.id,
                        error: {},
                        rule,
                        ruleId: rule.id,
                    });
                });

                this.selectedRuleId = null;
            }

            this.removeFieldError();
            this.showRuleSelection = false;
        },
    }
};
