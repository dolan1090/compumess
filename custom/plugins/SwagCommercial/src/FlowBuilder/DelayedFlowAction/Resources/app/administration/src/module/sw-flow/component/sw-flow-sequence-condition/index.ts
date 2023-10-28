import template from './sw-flow-sequence-condition.html.twig';
import {SEQUENCE_TYPES} from '../../../../constant/sw-flow-delay.constant';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import {WarningConfig} from "../../../../type/types";
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

const { Component, State } = Shopware;
const { Criteria } = Shopware.Data;
const utils = Shopware.Utils;

const { mapState } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['flowBuilderService'],

    computed: {
        delayConstant() {
            return this.flowBuilderService.getActionName('DELAY');
        },

        delayedActionsRepository(): RepositoryType {
            return this.repositoryFactory.create('swag_delay_action');
        },

        delayedActionsCriteria(): CriteriaType {
            const criteria = new Criteria(1, 1);

            criteria.addFilter(Criteria.equalsAny('delaySequenceId', this.getDelayIds(this.sequence.id)));

            return criteria;
        },

        ...mapState('swFlowDelay', ['showWarningModal']),
    },

    watch: {
        showWarningModal(value: WarningConfig): void {
            if (value.name === SEQUENCE_TYPES.CONDITION && this.sequence.id === value.id && value.actionType === 'ADD') {
                this.$super('onRuleChange', value.rule);
            }

            if (value.name === SEQUENCE_TYPES.CONDITION && this.sequence.id === value.id && value.actionType === 'DELETE') {
                this.$super('removeCondition');
            }
        },
    },

    methods: {
        addDelayAction(trueCase: boolean): void {
            let sequence = this.sequenceRepository.create();
            const newSequence = {
                ...sequence,
                parentId: this.sequence.id,
                displayGroup: this.sequence.displayGroup,
                actionName: 'action.delay',
                ruleId: null,
                config: {},
                position: 1,
                trueCase: trueCase,
                id: utils.createId(),
            };

            sequence = Object.assign(sequence, newSequence);
            State.commit('swFlowState/addSequence', sequence);
        },

        hasDelay(sequences: EntityCollection<'flow_sequence'>, parentId: string): boolean {
            const parentSequence = sequences.find(item => item.id === parentId);
            if (!parentSequence) return false;
            if (parentSequence.actionName === this.delayConstant) return true;
            return this.hasDelay(sequences, parentSequence.parentId);
        },

        getDelayIds(id: string): Array<string> {
            const delayedSequences = [];
            const getChildren = (currentId, arr) => {
                const childSequences = this.sequences.filter(sequence => sequence.parentId === currentId);
                if (!childSequences.length) {
                    return [];
                }


                return childSequences.forEach(item => {
                    if (!item.actionName && !item.ruleId) {
                        return [];
                    }

                    if (item.ruleId) {
                        return getChildren(item.id, arr);
                    }

                    if (item.actionName === this.delayConstant) {
                        arr.push(item.id);
                    }

                    return getChildren(item.id, arr);
                });
            }

            getChildren(id, delayedSequences);
            return delayedSequences;
        },

        async getDelayedActionData(): Promise<[]> {
            if (!this.getDelayIds(this.sequence.id).length) {
                return [];
            }

            try {
                return await this.delayedActionsRepository.search(this.delayedActionsCriteria);
            } catch (error) {
                return [];
            }
        },

        onRuleChange(rule: Entity<'rule'>): void {
            if (!rule) {
                return;
            }
            if (this.hasDelay(this.sequences, this.sequence.parentId) && localStorage.getItem('condition') !== 'true') {
                State.commit('swFlowDelay/setShowWarningModal', { actionType: 'ADD', type: SEQUENCE_TYPES.CONDITION, id: this.sequence.id, enabled: true, rule });
            } else {
                this.$super('onRuleChange', rule);
            }
        },

        async removeCondition(): Promise<void> {
            const delayedData = await this.getDelayedActionData();

            if (delayedData.length || (this.hasDelay(this.sequences, this.sequence.parentId) && localStorage.getItem('condition') !== 'true')) {
                State.commit('swFlowDelay/setShowWarningModal', { actionType: 'DELETE', type: SEQUENCE_TYPES.CONDITION, id: this.sequence.id, enabled: true });
            } else {
                this.$super('removeCondition');
            }
        },
    }
});
