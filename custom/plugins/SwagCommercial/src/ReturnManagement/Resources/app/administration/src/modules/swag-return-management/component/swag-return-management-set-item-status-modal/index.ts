import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';
import type CriteriaType from '@administration/core/data/criteria.data';

import template from './swag-return-management-set-item-status-modal.html';
import { LineItemStatus } from '../../../../type/types.d';

const { Component, State } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * @package checkout
 */
interface LineItemStateOption {
    name: string,
    id: string,
    disabled: boolean
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'stateStyleDataProviderService',
        'orderReturnApiService',
    ],

    props: {
        entityName: {
            type: String,
            required: true,
        },

        lineItems: {
            type: Array as PropType<Array<Entity<'order_line_item'>|Entity<'order_return_line_item'>>>,
            required: true,
        },

        excludedStates: {
            type: Array as PropType<string[]>,
            required: false,
            default() {
                return [];
            },
        },
    },

    data():{
        isLoading: boolean,
        lineItemStateOptions: Array<LineItemStateOption>,
        selectedState: LineItemStateOption | null,
    } {
        return {
            isLoading: false,
            lineItemStateOptions: [],
            selectedState: null
        };
    },

    computed: {
        versionId(): string {
            return State.get('swOrderDetail').versionContext.versionId;
        },

        modalTitle(): string {
            let content = this.lineItems.length;

            if (this.lineItems.length === 1) {
                content = this.lineItems[0].label || this.lineItems[0].lineItem.label;
            }

            return this.$tc('swag-return-management.setStatusModal.title', this.lineItems.length, { content });
        },

        stateMachineStateRepository(): RepositoryType<'state_machine_state'> {
            return this.repositoryFactory.create('state_machine_state');
        },

        stateMachineStateCriteria(): CriteriaType {
            const criteria = new Criteria(1, null);
            criteria.addSorting({ field: 'name', order: 'ASC' });
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equals(
                    'state_machine_state.stateMachine.technicalName',
                    'order_line_item.state',
                ),
            );

            if (this.excludedStates.length > 0) {
                criteria.addFilter(Criteria.not('AND', [
                    Criteria.equalsAny('state_machine_state.technicalName', this.excludedStates),
                ]));
            }

            return criteria;
        }
    },

    created(): void {
        const currentState = this.lineItems[0].state
            || this.lineItems[0].extensions?.state;

        this.selectedState = {
            name: currentState?.translated?.name ?? '',
            id: currentState?.technicalName ?? '',
        };

        this.loadStateMachineState();
    },

    methods: {
        async onSave(): Promise<void> {
            const payload = {
                ids: this.lineItems.map(item => item.id)
            };

            this.isLoading = true;

            try {
                if (this.entityName === 'orderReturnLineItem') {
                    await this.changeStateOrderReturnLineItem(payload);
                } else if (this.entityName === 'orderLineItem') {
                    await this.changeStateOrderLineItem(payload);
                }

                this.$emit('set-status-success');
            } catch(error) {
                this.createStateChangeErrorNotification(error);
            } finally {
                this.isLoading = false;
            }
        },

        onCancel(): void {
            this.$emit('modal-close');
        },

        onStateSelect(stateType: string, actionName: string): void {
            if (!stateType || !actionName) {
                this.createStateChangeErrorNotification(this.$tc('swag-return-handling.notification.labelErrorNoAction'));
                return;
            }

            this.selectedState = this.lineItemStateOptions.find(item => item.id === actionName);
        },

        backgroundStyle(stateType: string): Promise<string> {
            if (!this.selectedState?.id) {
                return null;
            }

            return this.stateStyleDataProviderService.getStyle(
                `${stateType}.state`,
                this.selectedState.id,
            ).selectBackgroundStyle;
        },

        createStateChangeErrorNotification(errorMessage: string): void {
            this.createNotificationError({
                message: this.$tc('swag-return-handling.notification.labelErrorStateChange') + errorMessage,
            });
        },

        loadStateMachineState(): Promise<void> {
            return this.stateMachineStateRepository.search(this.stateMachineStateCriteria)
                .then((data) => {
                    this.lineItemStateOptions = data.map(item => {
                        return {
                            name: item?.translated?.name || item.name,
                            id: item.technicalName,
                            disabled: this.selectedState.id === LineItemStatus.CANCELLED
                                && [LineItemStatus.RETURNED, LineItemStatus.RETURNED_PARTIALLY].includes(item.technicalName)
                        };
                    });

                    const existedState = this.lineItemStateOptions.find(item => item.id === this.selectedState.id);

                    if (existedState) {
                        this.selectedState = {
                            name: existedState.name,
                            id: existedState.technicalName,
                        };
                    }
                });
        },

        changeStateOrderReturnLineItem(payload: { ids: string[]}): Promise<void> {
            return this.orderReturnApiService.changeStateOrderReturnLineItem(this.selectedState.id, payload, this.versionId);
        },

        changeStateOrderLineItem(payload: { ids: string[]}): Promise<void> {
            return this.orderReturnApiService.changeStateOrderLineItem(this.selectedState.id, payload, this.versionId);
        }
    },
});
