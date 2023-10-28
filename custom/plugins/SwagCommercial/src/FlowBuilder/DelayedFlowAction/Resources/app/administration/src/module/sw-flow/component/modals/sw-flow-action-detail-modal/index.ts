import type {PropType} from 'vue';
import template from './sw-flow-action-detail-modal.html';
import './sw-flow-action-detail-modal.scss';
import {ActionConfig, ActionOption, DelayType} from '../../../../../type/types';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapGetters, mapState } = Component.getComponentHelper();

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['flowBuilderService', 'repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        sequence: {
            type: Object as PropType<Entity<'flow_sequence'>>,
            default: null
        },

        appFlowActions: {
            type: Array as PropType<ActionOption>,
            default: [],
        },
    },

    data(): {
        sequenceTree: [],
        actionsTrueCase: [],
        actionsFalseCase: [],
        isLoading: boolean,
    } {
        return {
            sequenceTree: [],
            actionsTrueCase: [],
            actionsFalseCase: [],
            isLoading: false,
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        flowSequenceRepository(): RepositoryType<'flow_sequence'> {
            return this.repositoryFactory.create('flow_sequence');
        },

        flowSequenceCriteria(): CriteriaType {
            const criteria = new Criteria();
            criteria.addAssociation('children');
            criteria.addAssociation('rule');
            criteria.addSorting(Criteria.sort('position', 'ASC'));
            criteria.getAssociation('children').addAssociation('rule');
            criteria.getAssociation('children').addSorting(Criteria.sort('position', 'ASC'));

            return criteria;
        },

        flowSequenceCriteriaChildren(): CriteriaType {
            const parentCriteria = Criteria.fromCriteria(this.flowSequenceCriteria).setLimit(1);
            parentCriteria.addSorting(Criteria.sort('position', 'ASC'));
            parentCriteria.associations.push({
                association: 'children',
                criteria: Criteria.fromCriteria(this.flowSequenceCriteria),
            });

            return parentCriteria;
        },

        delayConstant() {
            return this.flowBuilderService.getActionName('DELAY');
        },

        isActionDetail(): boolean {
            return this.sequenceTree?.length >= 1 && this.sequenceTree[0].actionName;
        },

        conditionName(): string {
            if (!this.sequenceTree?.length) {
                return '';
            }

            return this.sequenceTree.first().rule?.name || '';
        },

        getActionTitle(): string {
            if (!this.sequenceTree?.length) {
                return '';
            }

            const firstSequence = this.sequenceTree.first();
            if (firstSequence.actionName === this.delayConstant) {
                return this.$tc('sw-flow-delay.delay.itemDetail.delay')
            }

            return this.$tc('sw-flow-delay.delay.itemDetail.actions');
        },

        ...mapState(
            'swFlowState',
            [
                'stateMachineState',
                'documentTypes',
                'mailTemplates',
                'customerGroups',
                'customFieldSets',
                'customFields',
            ],
        ),
        ...mapGetters(
            'swFlowState', ['appActions'],
        ),
    },

    methods: {
        createdComponent(): void {
            this.getDetail();
        },

        getDetail(): void {
            this.isLoading = true;
            this.flowSequenceRepository.get(this.sequence.delaySequenceId, Shopware.Context.api, this.flowSequenceCriteriaChildren)
                .then((result) => {
                    this.sequenceTree = result?.children;
                }).catch(() => {
                this.createNotificationError({
                    message: this.$tc('sw-flow-delay.delay.list.fetchErrorMessage'),
                });
            }).finally(() => {
                this.isLoading = false;
            });
        },

        actionCases(trueCase = true): [] {
            if (!this.sequenceTree?.length || !this.sequenceTree[0]?.children) return [];
            return this.sequenceTree[0].children.filter(action => action.trueCase === trueCase);
        },

        getActionDescriptions(sequence: Entity<'flow_sequence'>): string {
            const { actionName, config } = sequence;
            if (actionName === this.delayConstant) {
                return this.getDelayDescription(config);
            }

            const data = {
                appActions: this.appActions,
                customerGroups: this.customerGroups,
                customFieldSets: this.customFieldSets,
                customFields: this.customFields,
                stateMachineState: this.stateMachineState,
                documentTypes: this.documentTypes,
                mailTemplates: this.mailTemplates,
            };

            return this.flowBuilderService.getActionDescriptions(data, sequence, this);
        },

        getDelayDescription(config: ActionConfig): string {
            const unit = this.getTimeLabel(config['delay'][0].type, config['delay'][0].value);
            return `${this.$tc('sw-flow-delay.delay.itemDetail.delayed')}: ${config['delay'][0].value} ${unit}`;
        },

        getTimeLabel(type: DelayType, number: number): string {
            switch (type) {

                case 'hour': {
                    return this.$tc('sw-flow-delay.modal.labelHour', number);
                }

                case 'day': {
                    return this.$tc('sw-flow-delay.modal.labelDay', number);
                }

                case 'week':{
                    return this.$tc('sw-flow-delay.modal.labelWeek', number);
                }

                case 'month': {
                    return this.$tc('sw-flow-delay.modal.labelMonth', number);
                }

                default: return '';
            }
        },

        onCloseModal() {
            this.$emit('modal-close');
        },
    },
});
