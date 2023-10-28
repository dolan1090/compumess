import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';

import template from './swag-return-management-item-detail-modal.html';

const { Component, Mixin, Utils } = Shopware;
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

    inject: [
        'acl',
        'repositoryFactory',
        'orderReturnApiService',
    ],

    props: {
        item: {
            type: Object as PropType<Entity<'order_return_line_item'>>,
            required: true,
        },
    },

    data():{
        isLoading: boolean,
    } {
        return {
            isLoading: false,
        };
    },

    computed: {
        ...mapState('swOrderDetail', [
            'order',
            'versionContext',
        ]),

        currencySymbol(): string {
            return this.order?.currency?.symbol || '';
        },

        taxStatus(): string {
            return this.item?.price?.taxStatus || '';
        },

        showTaxValue(): string {
            return `${this.item?.price?.taxRules[0]?.taxRate} %`;
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

        modalTitle(): string {
            return this.item?.lineItem?.label;
        },

        orderReturnLineItemRepository(): RepositoryType<'order_return_line_item'> {
            return this.repositoryFactory.create('order_return_line_item');
        },

        maxQuantity(): number|null {
            return this.item?.lineItem?.quantity ?? null;
        },
    },

    methods: {
        async onSave(): Promise<void> {
            this.isLoading = true;

            try {
                await this.orderReturnLineItemRepository.save(this.item, this.versionContext);
                await this.orderReturnApiService.recalculateRefundAmount(this.item.orderReturnId, this.versionContext.versionId);
                this.$emit('update-item-success');
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc('swag-return-management.notification.labelErrorUpdateRefund'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        onCancel(): void {
            if (!this.orderReturnLineItemRepository.hasChanges(this.item)) {
                this.orderReturnLineItemRepository.discard(this.item);
            }

            this.$emit('modal-close');
        },

        getSuggestedPrice(item: Entity<'order_return_line_item'>): string {
            let totalDiscount = 0;

            item?.discount?.forEach(info => {
                const discountForItem = info.discount * item.quantity / info.quantity;
                totalDiscount += discountForItem;
            });

            const suggestedPrice = item.price.totalPrice - totalDiscount;

            return this.$tc('swag-return-management.returnItemGrid.tooltipSuggestedPrice', 0 , {
                price: format.currency(suggestedPrice, this.order?.currency?.shortName, 2)
            });
        },
    },
});
