import type {PropType} from 'vue';
import template from './sw-flow-delay-edit-warning-modal.html';
import './sw-flow-delay-edit-warning-modal.scss';
import {SEQUENCE_TYPES} from '../../../../../constant/sw-flow-delay.constant';
import {NotificationType} from "../../../../../type/types";

const { Component } = Shopware;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    data(): {
        dontRemindSelection: boolean
    } {
        return {
            dontRemindSelection: false,
        }
    },

    computed: {
        titleModal(): string {
            if (this.type === SEQUENCE_TYPES.ACTION || this.type === SEQUENCE_TYPES.CONDITION) {
                return this.$tc('global.default.warning');
            }

            if (this.type === SEQUENCE_TYPES.DELAY_ACTION && this.actionType === 'DELETE') {
                return this.$tc('global.default.warning');
            }

            return this.$tc('global.default.info');
        },

        warningContent(): NotificationType {
            if (this.type === SEQUENCE_TYPES.ACTION) {
                return {
                    text: this.$tc('sw-flow-delay.detail.sequence.labelChangingAction'),
                    type: 'warning'
                }
            }

            if (this.type === SEQUENCE_TYPES.CONDITION) {
                return {
                    text: this.$tc('sw-flow-delay.detail.sequence.labelChangingCondition'),
                    type: 'warning'
                }
            }

            if (this.type === SEQUENCE_TYPES.DELAY_ACTION && this.actionType !== 'DELETE') {
                return {
                    text: this.$tc('sw-flow-delay.detail.sequence.labelChangingDelay'),
                    type: 'info'
                }
            }

            return {
                text: this.$tc('sw-flow-delay.detail.sequence.labelDeletingDelay'),
                type: 'warning'
            }
        },
    },

    props: {
        actionType: {
            type: String,
            default: 'DELETE',
        },

        type: {
            type: String as PropType<SEQUENCE_TYPES.ACTION>,
            default: SEQUENCE_TYPES.ACTION,
        },
    },

    methods: {
        handleCloseModal(): void {
            if (this.dontRemindSelection && this.type === SEQUENCE_TYPES.DELAY_ACTION && this.actionType === 'DELETE') {
                localStorage.setItem('delay_deleted', 'true');
                this.$emit('modal-close');
                return;
            }

            if (this.dontRemindSelection) {
                localStorage.setItem(this.type, 'true');
            }

            this.$emit('modal-close');
        },
    }
});
