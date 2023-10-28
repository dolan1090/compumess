import template from './sw-flow-sequence-modal-error.html.twig';
import './sw-flow-sequence-modal-error.scss';
import type {ErrorData} from '../../flow.types';
import {ACTION_GROUP_MISSING_ERROR, ACTION} from '../../../../constant/flow-sharing.constant';

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

        errorData(): ErrorData {
            switch (this.sequence.actionName) {
                case ACTION.ADD_CUSTOMER_TAG:
                case ACTION.ADD_ORDER_TAG:
                case ACTION.REMOVE_CUSTOMER_TAG:
                case ACTION.REMOVE_ORDER_TAG:
                    return this.sequence.error.errorDetail.tag;
                case ACTION.SET_CUSTOMER_CUSTOM_FIELD:
                case ACTION.SET_ORDER_CUSTOM_FIELD:
                case ACTION.SET_CUSTOMER_GROUP_CUSTOM_FIELD:
                    return this.sequence.error.errorDetail.custom_field;
                case ACTION.CHANGE_CUSTOMER_GROUP:
                    return this.sequence.error.errorDetail.customer_group;
                case ACTION.MAIL_SEND:
                    return this.sequence.error.errorDetail.mail_template;
                default:
                    return {};
            }
        },
    },

    methods: {
        getErrorTitle(actionName: string): string {
            return this.$tc('sw-flow-sharing.importError.textMissingObject', { data: ACTION_GROUP_MISSING_ERROR[actionName] });
        },
    },
};
