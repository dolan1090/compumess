import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';
import { LineItemType } from '@administration/module/sw-order/order.types';
import { DiscountScopes, DiscountTypes } from '@administration/module/sw-promotion-v2/promotion.types';

import template from './swag-return-management-return-line-items-grid.html';
import './swag-return-management-return-line-items-grid.scss';

import type { GridColumn } from '../../../../type/types';

import { LineItemStatus } from '../../../../type/types.d';

const { Component, Utils, Mixin } = Shopware;
const { format } = Utils;
const { mapState } = Component.getComponentHelper();

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    inject: ['acl', 'repositoryFactory', 'orderReturnApiService'],

    props: {
        returnId: {
            type: String,
            required: true,
        },

        returnLineItems: {
            type: Array as PropType<Array<Entity<'order_return_line_item'>>>,
            required: true,
        },

        taxStatus: {
            type: String,
            required: true,
        }
    },

    data(): {
        isLoading: boolean,
        searchTerm: string,
        showItemStatusModal: boolean,
        showDeleteReturnItemModal: boolean,
        showItemDetailsModal: boolean,
        selectedActionItems: {
            [key: string]: Entity<'order_return_line_item'>
        },
        selectedItemOpenDetail: Entity<'order_return_line_item'>|null,
        selectedItems: {
            [key: string]: Entity<'order_return_line_item'>
        },
    } {
        return {
            isLoading: false,
            selectedItems: {},
            searchTerm: '',
            showItemStatusModal: false,
            showDeleteReturnItemModal: false,
            showItemDetailsModal: false,
            selectedActionItems: null,
            selectedItemOpenDetail: null,
        };
    },
    computed: {
        ...mapState('swOrderDetail', [
            'order',
            'versionContext'
        ]),

        orderReturnLineItemRepository(): RepositoryType {
            return this.repositoryFactory.create('order_return_line_item');
        },

        orderReturnLineItems(): Entity<'order_return_line_item'>[] {
            if (!this.searchTerm) {
                return this.returnLineItems;
            }

            // Filter based on the product label is not blank and contains the search term or not
            const keyWords = this.searchTerm.split(/[\W_]+/ig);
            return this.returnLineItems.filter(item => {
                if (!item.lineItem.label) {
                    return false;
                }

                return keyWords.every(key => item.lineItem.label.toLowerCase().includes(key.toLowerCase()));
            });
        },

        unitPriceLabel(): string {
            if (this.taxStatus === 'net') {
                return this.$tc('swag-return-management.returnItemGrid.columnPriceNet');
            }

            if (this.taxStatus === 'tax-free') {
                return this.$tc('swag-return-management.returnItemGrid.columnPriceTaxFree');
            }

            return this.$tc('swag-return-management.returnItemGrid.columnPriceGross');
        },

        getLineItemColumns(): GridColumn[] {
            const columnDefinitions = [{
                property: 'quantity',
                label: 'swag-return-management.returnItemGrid.columnQuantity',
                allowResize: false,
                align: 'right',
                width: '90px',
                inlineEdit: true,
            }, {
                property: 'lineItem.label',
                label: 'swag-return-management.returnItemGrid.columnProductName',
                allowResize: false,
                primary: true,
                multiLine: true,
            }, {
                property: 'state.name',
                label: 'swag-return-management.returnItemGrid.columnStatus',
                allowResize: false,
                multiLine: true,
            }, {
                property: 'price.unitPrice',
                label: this.unitPriceLabel,
                allowResize: false,
                align: 'right',
                width: '120px',
            }];

            if (this.taxStatus !== 'tax-free') {
                columnDefinitions.push({
                    property: 'price.taxRules[0]',
                    label: 'swag-return-management.returnItemGrid.columnTax',
                    allowResize: false,
                    align: 'right',
                    width: '90px',
                });
            }

            return [...columnDefinitions, {
                    property: 'price.totalPrice',
                    label: 'swag-return-management.returnItemGrid.columnSubTotal',
                    allowResize: false,
                    align: 'right',
                    width: '120px',
                }, {
                    property: 'refundAmount',
                    label: 'swag-return-management.returnItemGrid.columnRefund',
                    allowResize: false,
                    align: 'right',
                    width: '120px',
                    inlineEdit: true,
                }
            ];
        },

        showDiscountWarning(): boolean {
            return this.discountLineItems.length > 0 && this.returnLineItems.length > 0;
        },

        discountLineItems(): Entity<'order_line_item'>[] {
            return this.order?.lineItems?.filter(item =>
                item.type === LineItemType.PROMOTION || item.type === LineItemType.CREDIT);
        },

        excludedStates(): string[] {
            return [
                LineItemStatus.OPEN, LineItemStatus.SHIPPED, LineItemStatus.SHIPPED_PARTIALLY, LineItemStatus.CANCELLED
            ];
        }
    },

    created() {
        this.discountLineItems.forEach((discountLineItem: Entity<'order_line_item'>) => {
            if (discountLineItem.type === LineItemType.CREDIT) {
                return;
            }

            discountLineItem?.payload?.composition?.forEach(discount => {
                this.returnLineItems.forEach((orderReturnLineItem, index) => {
                    if (discount.id !== orderReturnLineItem.lineItem.identifier) {
                        return;
                    }

                    if (!this.returnLineItems[index].discount) {
                        this.returnLineItems[index].discount = [];
                    }

                    this.returnLineItems[index].discount.push(discount);
                });
            });
        });
    },

    methods: {
        onSelectionChange(selection: { [key:string]: Entity<'order_return_line_item'> }): void {
            this.selectedItems = selection;
        },

        onChangeQuantity(newQuantity: number, item: Entity<'order_return_line_item'>): void {
            item.quantity = newQuantity;
            item.refundAmount = item.price.unitPrice * item.quantity;
        },

        showTaxValue(item: Entity<'order_return_line_item'>): string {
            return `${item.price.taxRules[0].taxRate} %`;
        },

        onDeleteItem(item: Entity<'order_return_line_item'>) {
            this.selectedActionItems = item ? [item] : Object.values(this.selectedItems);
            this.showDeleteReturnItemModal = true;
        },

        onSearchTermChange(searchTerm: string): void {
            this.searchTerm = searchTerm.toLowerCase();
        },

        isProductItem(item: Entity<'order_line_item'>): boolean {
            return item.type === LineItemType.PRODUCT;
        },

        getItemStatus(item: Entity<'order_return_line_item'>): string {
            return item?.state?.translated?.name ?? '';
        },

        getItemStatusTechnicalName(item: Entity<'order_return_line_item'>): string {
            return item?.state?.technicalName ?? '';
        },

        onSetItemStatus(item: Entity<'order_return_line_item'>): void {
            this.selectedActionItems = item ? [item] : Object.values(this.selectedItems);
            this.showItemStatusModal = true;
        },

        onOpenItemDetail(item: Entity<'order_return_line_item'>): void {
            this.selectedItemOpenDetail = item;
            this.showItemDetailsModal = true;
        },

        onCloseItemDetailModal(): void {
            this.selectedItemOpenDetail = null;
            this.showItemDetailsModal = false;
        },

        onCloseItemStatusModal(): void {
            this.showItemStatusModal = false;
            this.selectedChangeStatusItem = null;
        },

        updateStatusOrderLineItem(transition: string, item: Entity<'order_return_line_item'>): Promise<void> {
            return this.orderReturnApiService.changeStateOrderLineItem(
                transition,
                { ids: [item.lineItem.id] },
                this.versionContext.versionId
            );
        },

        async updateRefundAmount(item: Entity<'order_return_line_item'>): Promise<void> {
            try {
                await this.orderReturnLineItemRepository.save(item, this.versionContext);
                await this.orderReturnApiService.recalculateRefundAmount(this.returnId, this.versionContext.versionId);
                if (this.getItemStatusTechnicalName(item) !== LineItemStatus.RETURNED) {
                    this.$emit('reload-data');
                    return;
                }

                const transition = item.quantity === this.getItemMaxQuantity(item)
                    ? LineItemStatus.RETURNED
                    : LineItemStatus.RETURNED_PARTIALLY;
                await this.updateStatusOrderLineItem(transition, item);

                this.$emit('reload-data');
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc('swag-return-management.notification.labelErrorUpdateRefund') + error,
                });
            }
        },

        onSetStatusSuccess(): void {
            this.onCloseItemStatusModal();
            this.$refs.dataGrid.resetSelection();
            this.$emit('reload-data');
        },

        onDeleteItemSuccess(): void {
            this.onCloseDeleteReturnItemModal();
            this.$refs.dataGrid.resetSelection();
            this.$emit('reload-data');
        },

        onCloseDeleteReturnItemModal(): void {
            this.showDeleteReturnItemModal = false;
            this.selectedChangeStatusItem = null;
        },

        onUpdateItemSuccess(): void {
            this.onCloseItemDetailModal();
            this.$emit('reload-data');
        },

        onInlineEditCancel(item: Entity<'order_return_line_item'>): void {
            if (!this.orderReturnLineItemRepository.hasChanges(item)) {
                return;
            }

            this.orderReturnLineItemRepository.discard(item);
        },

        onInlineEditSave(item: Entity<'order_return_line_item'>): void {
            if (!this.orderReturnLineItemRepository.hasChanges(item)) {
                return;
            }

            this.updateRefundAmount(item);
        },

        getDescription(item: Entity<'order_line_item'>): string {
            const { totalPrice } = item.price;
            const currencyName = this.order?.currency?.shortName;

            if (item.type === LineItemType.CREDIT) {
                return this.$tc('swag-return-management.returnItemGrid.discountWarning.textCreditDescription',
                    0, { value: format.currency(Math.abs(totalPrice), currencyName, 2) });
            }

            const { value, discountScope, discountType, groupId } = item.payload;
            const snippet = `sw-order.createBase.textPromotionDescription.${discountScope}`;

            if (discountScope === DiscountScopes.CART &&
                discountType === DiscountTypes.ABSOLUTE &&
                Math.abs(totalPrice) < value) {
                return this.$tc(`${snippet}.absoluteUpto`, 0, {
                    value: format.currency(Number(value), currencyName, 2),
                    totalPrice: format.currency(Math.abs(totalPrice), currencyName, 2),
                });
            }

            const discountValue = discountType === DiscountTypes.PERCENTAGE
                ? value
                : format.currency(Number(value), currencyName, 2);

            return this.$tc(`${snippet}.${discountType}`, 0, { value: discountValue, groupId });
        },

        getSuggestedPrice(item: Entity<'order_return_line_item'>): string {
            let totalDiscount = 0;

            item?.discount?.forEach(info => {
                const discountForItem = info.discount * item.quantity / info.quantity;
                totalDiscount += discountForItem;
            });

            const suggestedPrice = item.price.totalPrice - totalDiscount;

            return this.$tc('swag-return-management.returnItemGrid.tooltipSuggestedPrice', 0 , {
                price: format.currency(suggestedPrice, this.order?.currency?.shortName, this.order?.totalRounding?.decimals)
            });
        },

        getItemMaxQuantity(item: Entity<'order_return_line_item'>): number|null {
            return item?.lineItem?.quantity ?? null;
        },

        showSuggestedPriceHelpText(item: Entity<'order_return_line_item'>): boolean {
            return item?.discount?.length > 0;
        },
    },
});
