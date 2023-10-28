import template from './sw-flow-sequence-error.html.twig';
import './sw-flow-sequence-error.scss';

/**
 * @package business-ops
 */
export default {
    template,
    props: {
        sequence: {
            type: Object,
            required: false,
            default: null,
        },
    },

    computed: {
        errorType(): string {
            return this.sequence?.error?.type || 'action';
        },

        isActionError(): boolean {
            return this.sequence?.error?.type === 'action';
        },

        isRuleError(): boolean {
            return this.sequence?.error?.type === 'rule';
        },

        isMissingRuleError(): boolean {
            return this.sequence?.error?.type === 'missing-rule';
        },
    },
};
