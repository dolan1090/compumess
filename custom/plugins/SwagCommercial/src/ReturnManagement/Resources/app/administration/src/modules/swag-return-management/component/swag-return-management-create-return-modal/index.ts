import type { PropType } from 'vue';
import type RepositoryType from '@administration/core/data/repository.data';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

import template from './swag-return-management-create-return-modal.html';
import './swag-return-management-create-return-modal.scss';
import { LineItemStatus } from '../../../../type/types.d';
import type { GridColumn } from '../../../../type/types';

const { Component, Mixin, Utils } = Shopware;
const { Criteria } = Shopware.Data;

interface ReturnLineItemPayload {
    orderLineItemId: string,
    quantity: number,
    internalComment: string,
}

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['acl', 'orderReturnApiService', 'repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        lineItems: {
            type: Array as PropType<Array<Entity<'order_line_item'>>>,
            required: true,
        },
        order: {
            type: Object,
            required: true,
        },
    },

    data(): {
        isLoading: boolean
    } {
        return {
            isLoading: false,
        };
    },

    computed: {
        orderReturn(): Entity<'order_return'>|null {
            return this.order?.extensions?.returns[0] ?? null;
        },

        lastChangedUser(): string {
            const { firstName = '', lastName = '' } = this.orderReturn?.updatedBy || this.orderReturn?.createdBy || {};

            return `${firstName} ${lastName}`.trim();
        },

        subtitle(): string|null {
            if (!this.orderReturn) {
                return null;
            }

            const time = Utils.format.date(this.orderReturn.createdAt, {
                hour: '2-digit',
                minute: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });

            return this.$tc('swag-return-management.returnModal.subtitle', 0, {
                returnNumber: this.orderReturn.returnNumber,
                state: this.orderReturn?.state?.name,
                time,
                user: this.lastChangedUser
            });
        },

        getLineItemColumns(): Array<GridColumn> {
            return [{
                property: 'label',
                label: this.$tc('swag-return-management.returnModal.columnName'),
                allowResize: false,
                width: '40%',
                multiLine: true,
            }, {
                property: 'quantity',
                label: this.$tc('swag-return-management.returnModal.columnOrderQuantity'),
                align: 'right',
                width: '10%',
            }, {
                property: 'returnQuantity',
                label: this.$tc('swag-return-management.returnModal.columnReturnQuantity'),
                width: '10%',
            },
            {
                property: 'comment',
                label: this.$tc('swag-return-management.returnModal.columnComment'),
                width: '40%',
                multiLine: true,
            }];
        },

        hasNoItems(): boolean {
            return this.lineItems?.length === 0;
        },

        hasNoAvailableItems(): boolean {
            return !!this.lineItems?.every(lineItem =>
                this.hasReturnLineItems(lineItem) || this.hasInvalidStates(lineItem));
        },

        returnRepository(): RepositoryType<'order_return'> {
            return this.repositoryFactory.create('order_return');
        },

        isNotCreatorPermission(): boolean {
            return !this.acl.can('order_return.creator') && !this.orderReturn;
        },

        tooltipSaveButton() {
            return {
                disabled: !this.isNotCreatorPermission,
                showOnDisabledElements: true,
                message: this.$tc('sw-privileges.tooltip.warning')
            };
        },
    },

    methods: {
        loadReturn(): Promise<void> {
            const criteria = new Criteria();
            criteria.addAssociation('lineItems.lineItem')
                .addAssociation('lineItems.state')
                .addAssociation('createdBy')
                .addAssociation('updatedBy');

            return this.returnRepository.get(this.orderReturn.id, Shopware.Context.api, criteria);
        },

        hasReturnLineItems(lineItem: Entity<'order_line_item'>): boolean {
            return !!lineItem?.extensions?.returns?.length
        },

        hasInvalidStates(lineItem: Entity<'order_line_item'>): boolean {
            const invalidStates = [
                LineItemStatus.RETURNED,
                LineItemStatus.RETURNED_PARTIALLY,
                LineItemStatus.CANCELLED
            ];

            return !!invalidStates.includes(lineItem?.extensions?.state?.technicalName);
        },

        onCancel(): void {
            this.$emit('modal-close');
        },

        onSave(): Promise<void> {
            this.isLoading = true;
            let lineItems = this.lineItems.filter(item => item.returnQuantity > 0);

            if (lineItems.length === 0) {
                this.createNotificationError({
                    message: this.$tc('swag-return-management.returnModal.messageErrorNoItemHasQuantity'),
                });

                this.isLoading = false;
                return;
            }

            lineItems = lineItems.map(item => {
                return <ReturnLineItemPayload>{
                    orderLineItemId: item.id,
                    quantity: item.returnQuantity,
                    internalComment: item.comment,
                };
            });

            if (this.orderReturn?.id) {
                return this.addItemsOrderReturn(lineItems);
            }

            return this.createOrderReturn(lineItems);
        },

        createOrderReturn(lineItems: ReturnLineItemPayload[]): Promise<void> {
            return this.orderReturnApiService.create(
                this.order.id,
                { lineItems },
                this.order.versionId,
            ).then(() => {
                this.$emit('return-create');
            }).catch((error) => {
                const errorDetailMsg = error?.response?.data?.errors[0]?.detail;
                this.createNotificationError({
                    message: errorDetailMsg,
                });
            }).finally(() => {
                this.isLoading = false;
            })
        },

        addItemsOrderReturn(lineItems: ReturnLineItemPayload[]): Promise<void> {
            return this.orderReturnApiService.addItems(
                this.order.id,
                this.orderReturn.id,
                { orderLineItems: lineItems },
                this.order.versionId,
            ).then(() => {
                this.loadReturn().then((orderReturn) => {
                    this.$emit('return-added-items', orderReturn);
                })
            }).catch((error) => {
                const errorDetailMsg = error?.response?.data?.errors[0]?.detail;
                this.createNotificationError({
                    message: errorDetailMsg,
                });
            }).finally(() => {
                this.isLoading = false;
            })
        },
    },
});
