import template from './sw-flow-detail.html.twig';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';

const { Component, State } = Shopware;
const { mapState } = Component.getComponentHelper();
const { Criteria } = Shopware.Data;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory', 'feature', 'flowBuilderService'],

    data(): {
        isOpenWarningModal: boolean,
        delayedActions: EntityCollection<'swag_delay_action'>,
    } {
        return {
            isOpenWarningModal: false,
            delayedActions: [],
        }
    },

    computed: {
        ...mapState('swFlowState', ['flow']),

        delayConstant(): string {
            return this.flowBuilderService.getActionName('DELAY');
        },

        hasDelayedActions(): EntityCollection<'flow_sequence'> {
            return this.sequences.some(item => item.actionName === this.delayConstant);
        },

        flowCriteria(): CriteriaType {
            const criteria = new Criteria();

            criteria.addAssociation('sequences.rule');
            criteria.getAssociation('sequences')
                .addSorting(Criteria.sort('displayGroup', 'ASC'))
                .addSorting(Criteria.sort('parentId', 'ASC'))
                .addSorting(Criteria.sort('trueCase', 'ASC'))
                .addSorting(Criteria.sort('position', 'ASC'));

            return criteria;
        },

        delayedActionsRepository(): RepositoryType<'swag_delay_action'> {
            return this.repositoryFactory.create('swag_delay_action');
        },

        delayedActionCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('flowId', this.flow.id));

            return criteria;
        },
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        async getDetailFlow(): Promise<void> {
            await this.$super('getDetailFlow');

            if (!this.isNewFlow && !this.isTemplate) {
                this.delayedActions = await this.delayedActionsRepository.search(this.delayedActionCriteria);
            }
        },

        validateEmptySequence(): EntityCollection<'flow_sequence'> {
            let invalidSequences = this.$super('validateEmptySequence');

            this.sequences.forEach((sequence) => {
                if(sequence.actionName === 'action.delay' && !sequence.config.delay) {
                    invalidSequences.push(sequence.id);
                }
            });

            State.commit('swFlowState/setInvalidSequences', invalidSequences);

            return invalidSequences;
        },

        onCloseModal(): void {
            this.isOpenWarningModal = false;
            this.flow.active = true;
        },

        async onSave(): Promise<void> {
            if ((typeof this.flow.isNew === 'function' && this.flow.isNew()) || this.isTemplate) {
                this.$super('onSave');
                return;
            }

            this.removeAllSelectors();

            const validDelayedActions = this.delayedActions.filter((delayedAction: Entity<'swag_delay_action'>) => {
                return this.sequences.some(sequence => sequence.parentId === delayedAction.delaySequenceId);
            });

            if (validDelayedActions.length > 0 && !this.flow.active) {
                this.isOpenWarningModal = true;
                return;
            }

            await this.$super('onSave');

            const invalidDelayedActions = this.delayedActions.filter(delayedAction => {
                return !validDelayedActions.some(validDelayedAction => validDelayedAction.id === delayedAction.id);
            });

            if (invalidDelayedActions.length > 0) {
                this.delayedActionsRepository.syncDeleted(invalidDelayedActions.getIds());
            }
        }
    }
});
