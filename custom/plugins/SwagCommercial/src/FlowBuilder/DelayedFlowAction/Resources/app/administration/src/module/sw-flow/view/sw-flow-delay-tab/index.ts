import template from './sw-flow-delay-tab.html';
import './sw-flow-delay-tab.scss';
import type RepositoryType from 'src/core/data/repository.data';
import type CriteriaType from 'src/core/data/criteria.data';
import {ActionOption, DelayActionColumn, Sequence, SortType} from "../../../../type/types";
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';

const { Component, Mixin, State, Service } = Shopware;
const { uniqBy } = Shopware.Utils.array;
const { Criteria } = Shopware.Data;
const { mapGetters, mapState } = Component.getComponentHelper();

const { date } = Shopware.Utils.format;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory', 'flowBuilderService'],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet')
    ],

    data(): {
        isLoading: boolean,
        total: number,
        delayedActions: Array<string>,
        sortBy: string,
        sortDirection: SortType,
        showModal: boolean,
        actionType: string,
        showExecuteAllDelayed: boolean,
        searchTerms: string,
        filterItems: Array<string>,
        delayedActionsFilter: Array<string>,
        showDetailActionsModal: boolean
    } {
        return {
            total: 0,
            isLoading: false,
            delayedActions: [],
            sortBy: 'executionTime',
            sortDirection: 'ASC',
            showModal: false,
            actionType: 'DELETE',
            searchTerms: null,
            showExecuteAllDelayed: false,
            filterItems: [],
            delayedActionsFilter: [],
            showDetailActionsModal: null,
        };
    },

    computed: {
        flow(): void {
            return State.get('swFlowState').flow;
        },

        isUnknownTrigger(): boolean {
            if (!this.$route.params.id) {
                return false;
            }

            return !this.triggerEvents.some((event) => {
                return event.name === this.flow.eventName;
            });
        },

        hasDelayedActions(): boolean {
            return this.sequences.some(item => item.actionName === 'action.delay');
        },

        isShowWarningAlert(): boolean {
            return this.isUnknownTrigger && !this.flow.active && this.hasDelayedActions;
        },

        delayedActionsRepository(): RepositoryType<'swag_delay_action'> {
            return this.repositoryFactory.create('swag_delay_action');
        },

        delayedActionColumns(): DelayActionColumn[] {
            return this.getDelayedActionColumns();
        },

        delayedActionFilterCriteria(): CriteriaType {
            const criteria = new Criteria();
            criteria.addAssociation('sequence.children.rule');
            criteria.addFilter(Criteria.equals('flowId', this.flow.id));
            criteria.addFilter(Criteria.not('and', [Criteria.equals('sequence.children.id', null)]));

            return criteria;
        },

        delayedActionCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);
            criteria
                .addAssociation('order')
                .addAssociation('customer')
                .addAssociation('sequence.children.rule');

            if (this.searchTerms) {
                criteria.addFilter(Criteria.multi('or', [
                    Criteria.contains('order.orderNumber', this.searchTerms),
                    Criteria.contains('customer.firstName', this.searchTerms),
                    Criteria.contains('customer.lastName', this.searchTerms),
                ]))
            }

            if (this.delayedActionsFilter.length > 0) {
                criteria.addFilter(Criteria.multi('or', [
                    Criteria.equalsAny('sequence.children.actionName', this.delayedActionsFilter),
                    Criteria.equalsAny('sequence.children.rule.name', this.delayedActionsFilter),
                ]));
            }

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addFilter(Criteria.equals('flowId', this.flow.id));
            criteria.addFilter(Criteria.not('and', [Criteria.equals('sequence.children.id', null)]));
            criteria.getAssociation('sequence.children').addSorting(Criteria.sort('position', 'ASC'));

            return criteria;
        },

        delayConstant() {
            return this.flowBuilderService.getActionName('DELAY');
        },

        ...mapState('swFlowState', ['triggerEvents']),
        ...mapGetters('swFlowState', ['appActions', 'sequences']),
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        getList(): Promise<[]> {
            if (this.getLicense('FLOW_BUILDER-1475275')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    'api/_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                            'Content-Type': 'application/json',
                            'sw-license-toggle': 'FLOW_BUILDER-1475275',
                        },
                    },
                );
                return;
            }

            this.isLoading = true;
            return this.delayedActionsRepository
                .search(this.delayedActionCriteria)
                .then((response) => {
                    this.delayedActions = response;
                    this.total = response.total;
                }).catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-flow-delay.delay.list.fetchErrorMessage'),
                    });
                }).finally(() => {
                    this.isLoading = false;
                    this.createActionFilter();
                });
        },

        getDelayedActionColumns(): DelayActionColumn[] {
            return [
                {
                    property: 'order.orderNumber',
                    dataIndex: 'order.orderNumber',
                    label: 'sw-flow-delay.delay.list.columnOrderNumber',
                    allowResize: false,
                },
                {
                    property: 'customer.firstName',
                    dataIndex: 'customer.firstName',
                    label: 'sw-flow-delay.delay.list.columnCustomer',
                    allowResize: false,
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: 'sw-flow-delay.delay.list.columnName',
                },
                {
                    property: 'executionTime',
                    dataIndex: 'executionTime',
                    label: 'sw-flow-delay.delay.list.columnRemainingTime',
                    allowResize: false,
                },
                {
                    property: 'scheduledFor',
                    dataIndex: 'executionTime',
                    label: 'sw-flow-delay.delay.list.columnScheduleFor',
                    allowResize: false,
                }];
        },

        onSearchTermChange(): void {
            this.getList();
        },

        dateFormat(ts: string): Date {
            return new Date(ts);
        },

        getScheduledFor(time: string): Date {
            const timestamps = new Date(time).getTime();
            return date(this.dateFormat(timestamps));
        },

        remainingTime(ts: string): string {
            const now = new Date().getTime();
            const endDate = new Date(ts).getTime();

            const t = endDate - now;

            if (t >= 0) {
                const days = Math.floor(t / (1000 * 60 * 60 * 24));
                const hours = Math.floor((t % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const mins = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));

                if (days >= 100) return `${days} ` + this.$tc('sw-flow-delay.delay.list.day', days);
                if (days >= 1 && days < 100) return `${("0" + days).slice(-2)} ` + this.$tc('sw-flow-delay.delay.list.day', days) + ` ${("0" + hours).slice(-2)}:${("0" + mins).slice(-2)} ` + this.$tc('sw-flow-delay.delay.list.hour', hours);
                if (days < 1 && hours >= 1) return `${("0" + hours).slice(-2)}:${("0" + mins).slice(-2)} ` + this.$tc('sw-flow-delay.delay.list.hour', hours);
                if (hours < 1) return `${("0" + mins).slice(-2)} ` + this.$tc('sw-flow-delay.delay.list.minute', mins);

            } else {
                // The results may already be filtered these cases from api
                return this.$tc('sw-flow-delay.delay.list.expiredAction')
            }
        },

        onAction(id: string, type: string): void {
            this.showModal = id;
            this.actionType = type;
        },

        onCloseModal(): void {
            this.showDetailActionsModal = false;
            this.showModal = false;
            this.showExecuteAllDelayed = false;
        },

        onConfirmAction(id: string): void | Promise<[]> {
            this.showModal = false;

            if (this.actionType === 'DELETE') {
                return this.delayedActionsRepository.delete(id).then(() => {
                    this.$refs.delayedActionsGrid.resetSelection();
                    this.getList();
                });
            }
            return this.delayedExecute([id]);
        },

        modalConfirmExecuteAll(): void {
            this.showExecuteAllDelayed = true;
        },

        onExecuteAll(ids: []): void {
            this.showExecuteAllDelayed = false;
            return this.delayedExecute(ids);
        },

        delayedExecute(ids: []): Promise<[]> {
            return Service('swFlowDelayService').delayedExecute(ids)
                .then(() => {
                    this.$refs.delayedActionsGrid.resetSelection();
                    this.getList();
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-flow-delay.delay.list.executeActionErrorMessage'),
                    });
                }).finally(() => {
                    this.isLoading = false;
                });
        },

        createActionFilter(): Promise<[]> {
            return this.delayedActionsRepository.search(this.delayedActionFilterCriteria).then((result) => {
                this.filterItems = this.getFilterItems(result);
            }).catch(() => {
                this.createNotificationError({
                    message: this.$tc('sw-flow-delay.delay.list.fetchErrorMessage'),
                });
            });
        },

        getFilterItems(actions: Sequence[]): Sequence[] {
            if (!actions.length) return [];
            let filters = [];
            actions.forEach(item => {
                const sequence = this.getSequence(item);
                filters.push(this.convertFilter(sequence));
            });

            return uniqBy(filters, 'value');
        },

        resetFilters(): void {
            this.delayedActionsFilter = [];
            this.getList();
        },

        detailActionsModal(item: Sequence): void {
            this.showDetailActionsModal = item.id;
        },

        getSequence(item: Sequence): Sequence | null {
            const { sequence: { children } } = item;
            if (!children.length) {
                return null;
            }

            return children[0];
        },

        convertFilter(sequence: Sequence): ActionOption {
            if (sequence.rule?.name){
                return {
                    label: sequence.rule?.name,
                    value: sequence.rule?.name,
                }
            }

            const appFlowAction: ActionOption = Object.values(this.appActions).find((item: Sequence) => item.name === sequence.actionName);
            if (appFlowAction) {
                return {
                    label: appFlowAction.translated?.label || appFlowAction.label,
                    value: appFlowAction.name,
                }
            }

            if (sequence.actionName === this.delayConstant) {
                return {
                    label: this.$tc('sw-flow-delay.detail.sequence.delayActionTitle'),
                    value: sequence.actionName,
                }
            }

            return {
                label: `${this.$tc(this.flowBuilderService.getActionTitle(sequence.actionName).label)}`,
                value: sequence.actionName,
            }
        },
    },
});
