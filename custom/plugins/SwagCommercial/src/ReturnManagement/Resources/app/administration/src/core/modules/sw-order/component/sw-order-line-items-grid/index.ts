import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import template from './sw-order-line-items-grid.html.twig';
import './sw-order-line-items-grid.scss';

import type { GridColumn } from '../../../../../type/types';
import { LineItemStatus } from '../../../../../type/types.d';
import { TOGGLE_KEY, TRAP_KEY_2 } from '../../../../../config';

const { Component, Mixin } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    data(): {
        showReturnModal: boolean,
        showItemStatusModal: boolean,
        showDeleteLineItemModal: boolean,
        returnItems: Entity<'order_line_item'>[],
        selectedChangeStatusItems: Entity<'order_line_item'>[]|null,
        deleteLineItems: Entity<'order_line_item'>[],
    } {
        return {
            returnItems: [],
            showReturnModal: false,
            showItemStatusModal: false,
            showDeleteLineItemModal: false,
            selectedChangeStatusItems: null,
            deleteLineItems: []
        }
    },

    computed: {
        hasOrderReturn(): boolean {
            return this.order?.extensions?.returns?.length > 0;
        },

        getLineItemColumns(): GridColumn[] {
            const columns = this.$super('getLineItemColumns');

            return [
                ...columns.slice(0, 2),
                {
                    property: 'extensions.state.name',
                    label: 'swag-return-management.returnItemGrid.columnStatus',
                    allowResize: false,
                },
                ...columns.slice(2),
            ];
        },

        returnId(): string {
            return this.order?.extensions?.returns[0]?.id || '';
        },

        returnItemButtonClass() {
            return {
                'is--disabled': !this.acl.can('order_return.editor')
            }
        },

        hasToggleKey() {
            return Shopware.License.get(TOGGLE_KEY);
        },
    },

    methods: {
        onInlineEditSave(item: Entity<'order_line_item'>): void {
            if (item.quantity < item.extensions?.returns[0]?.quantity) {
                this.orderLineItemRepository.discard(item);

                this.createNotificationError({
                    message: this.$tc('swag-return-management.notification.messageErrorLineItemQuantity', 0, {
                        returnQuantity: item.extensions?.returns[0]?.quantity,
                    }),
                });

                return;
            }

            return this.$super('onInlineEditSave', item);
        },

        isReturnableItem(lineItem: Entity<'order_line_item'>): boolean {
            if (!this.acl.can('order_return.editor')) {
                return false;
            }

            const invalidStatuses = [
                LineItemStatus.CANCELLED,
                LineItemStatus.RETURNED_PARTIALLY,
                LineItemStatus.RETURNED,
            ];

            return !this.isItemAddedToReturn(lineItem)
                && lineItem.type !== this.lineItemTypes.CREDIT
                && lineItem.type !== this.lineItemTypes.PROMOTION
                && !this.itemHasReturnStatus(invalidStatuses, lineItem)
                && !this.isDigitalProduct(lineItem);
        },

        isDigitalProduct(lineItem: Entity<'order_line_item'>): boolean {
            return lineItem.states?.includes('is-download');
        },

        onReturnSelectedItems(item: Entity<'order_line_item'>): void {
            if (!this.acl.can('order_return.editor')) {
                return;
            }

            if (Shopware.License.get(TRAP_KEY_2)) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    '_info/config',
                        {
                            headers: {
                                Accept: 'application/vnd.api+json',
                                Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                                    'Content-Type': 'application/json',
                                    'sw-license-toggle': TRAP_KEY_2,
                            },
                        },
                    );

                return;
            }

            const selectedItems = item
                ? [item]
                : Object.values(this.selectedItems);

            this.returnItems = Object.values(selectedItems).filter((item: Entity<'order_line_item'>) => {
                return item.type !== this.lineItemTypes.CREDIT
                    && item.type !== this.lineItemTypes.PROMOTION
                    && !this.isDigitalProduct(item);
            });

            this.showReturnModal = true;
        },

        onCloseReturnModal(): void {
            this.showReturnModal = false;
        },

        onReturnCreate(): void {
            this.showReturnModal = false;
            this.returnItems = [];
            this.$refs.dataGrid.resetSelection();
            this.$emit('save-edits');
        },

        onAddedItemsToReturn(orderReturn: Entity<'order_return'>): void {
            this.showReturnModal = false;
            this.returnItems = [];
            this.$refs.dataGrid.resetSelection();
            this.$emit('save-and-reload', orderReturn);
        },

        getItemStatus(item: Entity<'order_line_item'>): string {
            return item?.extensions?.state?.translated?.name ?? '';
        },

        showStatusItemAction(item: Entity<'order_line_item'>): boolean {
            return item.type !== this.lineItemTypes.CREDIT
                && item.type !== this.lineItemTypes.PROMOTION;
        },

        onSetItemStatus(item: Entity<'order_line_item'>): void {
            if (Shopware.License.get(TRAP_KEY_2)) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    '_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                                'Content-Type': 'application/json',
                                'sw-license-toggle': TRAP_KEY_2,
                        },
                    },
                );

                return;
            }

            const selectedItems = item
                ? [item]
                : Object.values(this.selectedItems);


            this.selectedChangeStatusItems = selectedItems.filter((item: Entity<'order_line_item'>) => {
                return this.showStatusItemAction(item);
            });

            if (!this.selectedChangeStatusItems.length) {
                this.createNotificationError({
                    message: this.$tc('swag-return-management.detail.messageErrorSetStatusModal')
                });

                return;
            }

            this.showItemStatusModal = true;
        },

        onCloseItemStatusModal(): void {
            this.showItemStatusModal = false;
            this.selectedChangeStatusItem = null;
        },

        onSetStatusSuccess(): void {
            this.showItemStatusModal = false;
            this.selectedChangeStatusItem = null;
            this.$refs.dataGrid.resetSelection();
            this.$emit('save-and-reload');
        },

        onDeleteSelectedItems(): void {
            const hasLineItemAddedToReturn = Object.values(this.selectedItems)
                .some((item: Entity<'order_line_item'>) => item?.extensions?.returns?.length > 0);

            if (hasLineItemAddedToReturn) {
                this.showDeleteLineItemModal = true;
                this.deleteLineItems = Object.values(this.selectedItems);

                return;
            }

            this.$super('onDeleteSelectedItems');
        },

        onDeleteItem(item: Entity<'order_line_item'>, itemIndex: number): void {
            if (item?.extensions?.returns?.length > 0) {
                this.showDeleteLineItemModal = true;
                this.deleteLineItems = [item];

                return;
            }

            this.$super('onDeleteItem', item, itemIndex);
        },

        onCloseDeleteLineItemModal(): void {
            this.showDeleteLineItemModal = false;
            this.deleteLineItems = [];
        },

        onDeleteItemHasReturn(): void {
            this.$emit('item-delete');
            this.$refs.dataGrid.resetSelection();
            this.showDeleteLineItemModal = false;
            this.deleteLineItems = [];
        },

        tooltipReturnContextMenu(item: Entity<'order_line_item'>): {
            message: string,
            showOnDisabledElements: boolean,
            disabled: boolean,
        } {
            return {
                message: this.acl.can('order_return.editor')
                    ? this.$tc('swag-return-management.detail.messageErrorCreateReturnModal')
                    : this.$tc('sw-privileges.tooltip.warning'),
                showOnDisabledElements: true,
                disabled: this.isReturnableItem(item),
            }
        },

        isItemAddedToReturn(item: Entity<'order_line_item'>): boolean {
            return item?.extensions?.returns?.length > 0;
        },

        itemHasReturnStatus(statuses: LineItemStatus[], item: Entity<'order_line_item'>): boolean {
            return statuses.includes(item?.extensions?.state?.technicalName);
        },

        showSetStatusManuallyWarning(item: Entity<'order_line_item'>): boolean {
            const returnStatuses = [
                LineItemStatus.RETURN_REQUESTED,
                LineItemStatus.RETURNED_PARTIALLY,
                LineItemStatus.RETURNED,
            ];

            return !this.isItemAddedToReturn(item)
                && this.itemHasReturnStatus(returnStatuses, item)
                && this.hasOrderReturn;
        },
    }
});
