import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import type RepositoryType from '@administration/core/data/repository.data';
import type CriteriaType from '@administration/core/data/criteria.data';

import template from './swag-return-management-return-card-state-history.html';

import type { GridColumn } from '../../../../type/types';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

interface StateHistory {
    createAt: Date,
    status: Entity<'state_machine_state'>,
    user: Entity<'user'>
}

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'stateStyleDataProviderService',
    ],

    props: {
        orderReturn: {
            type: Object as PropType<Entity<'order_return'>>,
            required: true,
        },
    },

    data(): {
        isLoading: boolean,
        stateHistories: Array<StateHistory>,
        limit: number,
        page: number,
        total: number,
        steps: number[]
    } {
        return {
            stateHistories: [],
            isLoading: false,
            limit: 10,
            page: 1,
            total: 0,
            steps: [5, 10, 25]
        };
    },

    computed: {
        stateMachineHistoryRepository(): RepositoryType<'state_machine_history'> {
            return this.repositoryFactory.create('state_machine_history');
        },

        statusHistoryColumns(): Array<GridColumn> {
            return [
                { property: 'createdAt', label: this.$tc('swag-return-management.returnCard.statusTab.columnDate') },
                { property: 'user', label: this.$tc('swag-return-management.returnCard.statusTab.columnUser') },
                { property: 'status', label: this.$tc('swag-return-management.returnCard.statusTab.columnStatus') },
            ];
        },

        stateMachineHistoryCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addFilter(
                Criteria.equals(
                    'state_machine_history.entityId.id',
                    this.orderReturn.id,
                ),
            );
            criteria.addFilter(
                Criteria.equals(
                    'state_machine_history.entityName',
                    'order_return',
                ),
            );

            criteria.addAssociation('fromStateMachineState');
            criteria.addAssociation('toStateMachineState');
            criteria.addAssociation('user');
            criteria.addSorting({ field: 'state_machine_history.createdAt', order: 'ASC' });

            return criteria;
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.getStateHistoryEntries();
        },

        getChangedByUserName(item): string {
            return item.user?.username ?? this.$tc('swag-return-management.returnCard.statusTab.labelSystemUser');
        },

        getStateHistoryEntries(): Promise<void> {
            this.isLoading = true;

            return this.stateMachineHistoryRepository.search(this.stateMachineHistoryCriteria)
                .then((stateHistories: EntityCollection<'state_machine_history'>) => {
                    this.total = stateHistories?.total ?? 1;

                    this.stateHistories = stateHistories.map(entry => {
                        return {
                            user: entry.user,
                            createdAt: entry.createdAt,
                            status: entry.toStateMachineState,
                        };
                    });

                    if (this.page === 1) {
                        this.stateHistories.unshift({
                            createdAt: this.orderReturn?.createdAt,
                            status: stateHistories[0]?.fromStateMachineState ?? this.orderReturn?.state,
                            user: this.orderReturn?.createdBy
                        });
                    }
                }).finally(() => {
                    this.isLoading = false;
                });
        },

        getVariantState(state: Entity<'state_machine_state'>): string {
            return this.stateStyleDataProviderService.getStyle('order_return.state', state.technicalName).variant;
        },

        onPageChange({ page, limit }): void {
            this.page = page;
            this.limit = limit;

            this.getStateHistoryEntries();
        },
    },
});
