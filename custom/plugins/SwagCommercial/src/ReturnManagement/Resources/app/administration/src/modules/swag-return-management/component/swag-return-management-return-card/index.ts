import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';
import type EntityCollectionType from '@administration/core/data/entity-collection.data';

import template from './swag-return-management-return-card.html';
import './swag-return-management-return-card.scss';

import type { CalculatedTax } from '../../../../type/types';

const { Component, Utils, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState } = Component.getComponentHelper();
const { cloneDeep } = Shopware.Utils.object;
const { format } = Utils;

/**
 * @package checkout
 */
interface OrderReturnStateOption {
    disabled: boolean,
    id: string,
    stateName: string,
    name: string
}

export default Component.wrapComponentConfig({
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    inject: [
        'acl',
        'repositoryFactory',
        'stateMachineService',
        'stateStyleDataProviderService',
        'orderReturnApiService',
    ],

    props: {
        item: {
            type: Object as PropType<Entity<'order_return'>>,
            required: true,
        },
    },

    data(): {
        returnItem: Entity<'order_return'>|null,
        isLoading: boolean,
        orderReturnStateOptions: Array<OrderReturnStateOption>,
        showDeleteReturnModal: boolean,
        showChangeStatusModal: boolean,
        selectedState: OrderReturnStateOption|null,
    } {
        return {
            returnItem: null,
            orderReturnStateOptions: [],
            isLoading: false,
            showDeleteReturnModal: false,
            showChangeStatusModal: false,
            selectedState: null,
        };
    },

    computed: {
        ...mapState('swOrderDetail', [
            'order',
            'versionContext'
        ]),

        taxStatus(): string {
            return this.returnItem?.price?.taxStatus || '';
        },

        returnRepository(): RepositoryType<'order_return'> {
            return this.repositoryFactory.create('order_return');
        },

        stateMachineStateRepository(): RepositoryType<'state_machine_state'> {
            return this.repositoryFactory.create('state_machine_state');
        },

        sortedCalculatedTaxes(): Array<CalculatedTax> {
            return this.sortByTaxRate(cloneDeep(this.returnItem?.price?.calculatedTaxes)).filter(price => price.tax !== 0);
        },

        cardDescription(): string {
            const time = Utils.format.date(this.returnItem?.createdAt, {
                hour: '2-digit',
                minute: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });

            return this.$tc('swag-return-management.returnCard.labelReturnCreated', 0,
                { time, user: this.lastChangedUser });
        },

        lastChangedUser(): string {
            if (this.returnItem?.updatedBy) {
                const { firstName, lastName } = this.returnItem.updatedBy;
                return `${firstName} ${lastName}`;
            }

            if (this.returnItem?.createdBy) {
                const { firstName, lastName } = this.returnItem.createdBy;
                return `${firstName} ${lastName}`;
            }

            return '';
        },

        returnLineItems(): Array<Entity<'order_return_line_item'>> {
            return this.returnItem?.lineItems ?? [];
        },

        totalItems(): number {
            return this.returnLineItems.reduce((total, lineItem) => total + lineItem.quantity, 0) ?? 0;
        },

        cardTitle(): string {
            return this.$tc('swag-return-management.returnCard.labelTitle', 0, { returnNumber: this.item.returnNumber})
        },

        stateSelectPlaceholder(): string {
            return this.returnItem?.state?.translated?.name || '';
        },

        shippingCostsDetail(): string {
            const calcTaxes = this.sortByTaxRate(cloneDeep(this.returnItem?.shippingCosts?.calculatedTaxes) ?? []);
            if (calcTaxes.length === 0) {
                return '';
            }

            const formattedTaxes = `${calcTaxes.map(
                calcTax => `${this.$tc('sw-order.detailBase.shippingCostsTax', 0, {
                    taxRate: calcTax.taxRate,
                    tax: format.currency(calcTax.tax, this.order.currency.shortName),
                })}`,
            ).join('<br>')}`;
            if (this.taxStatus === 'gross') {
                return `${this.$tc('sw-order.detailBase.tax')}<br>${formattedTaxes}`;
            }

            return `${this.$tc('swag-return-management.returnCard.excludedTax')}<br>${formattedTaxes}`;
        },
    },

    created(): void {
        this.createdComponent();
    },

    destroyed(): void {
        this.destroyedComponent();
    },

    methods: {
        createdComponent(): void {
            this.$root.$on('order-edit-cancel', this.onCancelEditing);
            this.returnItem = this.item;
            this.loadReturn();
            this.loadStateMachineState();
        },

        destroyedComponent(): void {
            this.$root.$off('order-edit-cancel', this.onCancelEditing);
        },

        backgroundStyle(stateType: string): Promise<string> {
            return this.stateStyleDataProviderService.getStyle(
                `${stateType}.state`,
                this?.returnItem?.state?.technicalName,
            ).selectBackgroundStyle;
        },

        async onStateSelect(stateType: string, actionName: string): Promise<void> {
            if (!stateType || !actionName) {
                this.createStateChangeErrorNotification(this.$tc('swag-return-management.notification.labelErrorNoAction'));
                return;
            }

            this.showChangeStatusModal = true;
            this.selectedState = this.orderReturnStateOptions.find(item => item.id === actionName);
        },

        loadReturn(): Promise<void> {
            this.isLoading = true;

            const criteria = new Criteria();
            criteria.addAssociation('state')
                    .addAssociation('lineItems.lineItem')
                    .addAssociation('lineItems.state')
                    .addAssociation('createdBy')
                    .addAssociation('updatedBy');

            return this.returnRepository.get(this.item.id, this.versionContext, criteria)
                .then(data => {
                    this.returnItem = data;
                }).finally(() => {
                    this.isLoading = false;
                });
        },

        loadStateMachineState(): Promise<void> {
            const criteria = new Criteria(1, null);
            criteria.addSorting({ field: 'name', order: 'ASC' });
            criteria.addAssociation('stateMachine');
            criteria.addFilter(
                Criteria.equals(
                    'state_machine_state.stateMachine.technicalName',
                    'order_return.state',
                ),
            );

            return this.stateMachineStateRepository.search(criteria)
                .then((data: EntityCollectionType) => {
                    const orderReturnStates = data as unknown as Array<Entity<'state_machine_state'>>;
                    this.getOrderLineItemStateTransition(orderReturnStates);
                });
        },

        sortByTaxRate(price: Array<CalculatedTax>): Array<CalculatedTax> {
            return price.sort((prev: CalculatedTax, current: CalculatedTax) => {
                return prev.taxRate - current.taxRate;
            });
        },

        getOrderLineItemStateTransition(orderReturnStates: Array<Entity<'state_machine_state'>>): Promise<void> {
            return this.stateMachineService.getState(
                'order_return',
                this.item.id,
                {},
                Shopware.Classes.ApiService.getVersionHeader(this.versionContext.versionId)
            )
                .then((response) => {
                    this.orderReturnStateOptions = this.buildTransitionOptions(
                        orderReturnStates,
                        response?.data?.transitions
                    );
                });
        },

        buildTransitionOptions(allTransitions: Array<Entity<'state_machine_state'>> = [], possibleTransitions: Array<Entity<'state_machine_transition'>> = []): Array<OrderReturnStateOption> {
            const options = allTransitions.map((state: Entity<'state_machine_state'>, index: number) => {
                return {
                    stateName: state.technicalName,
                    id: state.technicalName,
                    name: state.translated.name,
                    disabled: true,
                };
            });

            options.forEach((option: OrderReturnStateOption) => {
                const transitionToState = possibleTransitions.filter((transition: Entity<'state_machine_transition'>) => {
                    return transition.toStateName === option.stateName;
                });

                if (transitionToState.length >= 1) {
                    option.disabled = false;
                    option.id = transitionToState[0].actionName;
                }
            });

            return options;
        },

        createStateChangeErrorNotification(errorMessage: string): void {
            this.createNotificationError({
                message: this.$tc('swag-return-management.notification.labelErrorStateChange') + errorMessage,
            });
        },

        reloadData(): Promise<void> {
            return this.loadReturn().then(() => {
                this.$emit('reload-order');
            });
        },

        openDeleteReturnModal(): void {
            this.showDeleteReturnModal = true;
        },

        onCloseDeleteReturnModal(): void {
            this.showDeleteReturnModal = false;
        },

        onCloseChangeStatusModal(): void {
            this.showChangeStatusModal = false;
            this.selectedState = null;
        },

        onReturnStateChange(): void {
            this.showChangeStatusModal = false;
            this.selectedState = null;
            this.saveOrder();
        },

        onShippingChargeEdit(amount: number): void {
            this.returnItem.shippingCosts.quantity = 1;
            this.returnItem.shippingCosts.unitPrice = amount;
            this.returnItem.shippingCosts.totalPrice = amount;

            this.updateShippingCosts(this.returnItem);
        },

        async updateShippingCosts(item: Entity<'order_return'>): Promise<void> {
            try {
                await this.returnRepository.save(item, this.versionContext);
                await this.orderReturnApiService.recalculateRefundAmount(item.id, this.versionContext.versionId);
                await this.reloadData();
            } catch (error) {
                this.createNotificationError({
                    message: `${this.$tc('swag-return-management.notification.labelErrorUpdateRefund')}${error}`,
                });
            }
        },

        async onCancelEditing(): Promise<void> {
            await this.loadReturn();
            await this.loadStateMachineState();
        },

        saveOrder() {
            this.$emit('save-order');
        },
    },
});
