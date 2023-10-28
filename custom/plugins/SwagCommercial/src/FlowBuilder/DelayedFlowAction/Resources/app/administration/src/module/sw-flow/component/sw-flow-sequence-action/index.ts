import {SEQUENCE_TYPES} from '../../../../constant/sw-flow-delay.constant';
import {ActionOption, WarningConfig} from "../../../../type/types";
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

const { Component, State } = Shopware;
const { mapState, mapGetters } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    inject: ['flowBuilderService'],

    data(): {
        customSelectedAction: string,
        sequenceId: string
    } {
        return {
            customSelectedAction: null,
            sequenceId: null,
        };
    },

    computed: {
        delayConstant() {
            return this.flowBuilderService.getActionName('DELAY');
        },

        filterActionOptions(): Array<ActionOption> {
            let filterOptions = [];
            this.$super('actionOptions').filter(item => item && item.value !== this.delayConstant).forEach(item => {
                const option = this.triggerActions.find(action => item.value === action.name);

                if (!option.requirements.length) {
                    filterOptions.push(item);
                    return;
                }

                if (option.delayable) {
                    filterOptions.push(item);
                }
            })

            return filterOptions;
        },

        actionOptions(): Array<ActionOption> {
            const parentId = this.sequence.parentId || this.getParentId(this.sequence);
            if (!this.hasDelay(this.sequences, parentId)) {
                return this.$super('actionOptions').filter(item => item && item.value !== this.delayConstant);
            }

            return this.filterActionOptions;
        },

        ...mapState('swFlowState', ['triggerActions']),
        ...mapGetters('swFlowState', ['sequences', 'actionGroups']),
        ...mapState('swFlowDelay', ['showWarningModal']),
    },

    watch: {
        showWarningModal(value: WarningConfig): void {
            const sequenceId = this.sequence.id || this.getFirstKey(this.sequence);
            if (value.name === SEQUENCE_TYPES.ACTION && sequenceId === value.id && value.actionType === 'ADD') {
                this.selectedAction = this.customSelectedAction;
                State.commit('swFlowDelay/setShowWarningModal', { type: '', name: '', enabled: false, id: '' });
                this.$super('openDynamicModal', this.customSelectedAction);
            }

            if (value.name === SEQUENCE_TYPES.ACTION && value.actionType === 'EDIT') {
                this.$super('onEditAction', this.customSelectedAction, value.clickableOption.target, value.clickableOption.key);
            }

            if (value.name === SEQUENCE_TYPES.ACTION && value.actionType === 'DELETE') {
                this.$super('removeAction', value.id);
            }

            if (value.name === SEQUENCE_TYPES.ACTION && value.id === sequenceId && value.actionType === 'DELETE_ALL') {
                this.$super('removeActionContainer');
            }
        },
    },

    methods: {
        hasDelay(sequences: EntityCollection<'flow_sequence'>, parentId: string): boolean {
            const parentSequence = sequences.find(item => item.id === parentId);
            if (!parentSequence) return false;
            if (parentSequence.actionName === this.delayConstant) return true;
            return this.hasDelay(sequences, parentSequence.parentId);
        },

        getParentId(sequence: Entity<'flow_sequence'>): string {
            const lastKey = Object.keys(sequence)[Object.keys(sequence).length - 1];
            return sequence[lastKey]?.parentId || '';
        },

        getFirstKey(sequence: Entity<'flow_sequence'>): string {
            return Object.keys(sequence)[0];
        },

        openDynamicModal(value: string): void {
            if (!value) {
                return
            }

            if (value === 'action.stop.flow') {
                this.$super('openDynamicModal', value);
                return
            }

            const parentId = this.sequence.parentId || this.getParentId(this.sequence);
            if (this.hasDelay(this.sequences, parentId) && localStorage.getItem('action') !== 'true') {
                State.commit('swFlowDelay/setShowWarningModal', { actionType: 'ADD', type: SEQUENCE_TYPES.ACTION, name: '', enabled: true, id: this.sequence.id || this.getFirstKey(this.sequence) });
            } else {
                this.selectedAction = value;
                this.$super('openDynamicModal', value);
                return
            }

            this.customSelectedAction = value;
        },

        onEditAction(sequence: Entity<'flow_sequence'>, target, key): void {
            if (sequence.actionName && sequence.actionName === 'action.stop.flow') {
                return;
            }

            const parentId = sequence.parentId || this.getParentId(this.sequence);
            if (this.hasDelay(this.sequences, parentId) && localStorage.getItem('action') !== 'true') {
                State.commit('swFlowDelay/setShowWarningModal', {
                    actionType: 'EDIT',
                    type: SEQUENCE_TYPES.ACTION,
                    name: '',
                    enabled: true,
                    id: sequence.id,
                    clickableOption: {
                        target,
                        key
                    }
                });
                this.customSelectedAction = sequence;
            } else {
                this.$super('onEditAction', sequence, target, key);
            }
        },

        removeAction(id: string): void {
            const parentId = this.sequences.find(item => item.id === id).parentId;
            if (this.hasDelay(this.sequences, parentId) && localStorage.getItem('action') !== 'true') {
                State.commit('swFlowDelay/setShowWarningModal', { actionType: 'DELETE', type: SEQUENCE_TYPES.ACTION, name: '', enabled: true, id });
            } else {
                this.$super('removeAction', id);
            }
        },

        removeActionContainer(): void {
            const parentId = this.sequence.parentId || this.getParentId(this.sequence);
            if (this.hasDelay(this.sequences, parentId) && localStorage.getItem('action') !== 'true') {
                State.commit('swFlowDelay/setShowWarningModal', { actionType: 'DELETE_ALL', type: SEQUENCE_TYPES.ACTION, name: '', enabled: true, id: this.sequence.id || this.getFirstKey(this.sequence) });
            } else {
                this.$super('removeActionContainer');
            }
        },
    },
});
