import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';

import template from './swag-return-management-delete-return-item-modal.html';
import './swag-return-management-delete-return-item-modal.scss';

const { Component, Mixin } = Shopware;
const { mapState } = Component.getComponentHelper();

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
        'orderReturnApiService',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        returnId: {
            type: String,
            required: true,
        },

        items: {
            type: Array as PropType<Array<Entity<'order_return_line_item'>>>,
            required: true,
        },
        context: {
            type: Object,
            required: true
        }
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
            'versionContext'
        ]),

        orderReturnLineItemRepository(): RepositoryType<'order_return_line_item'> {
            return this.repositoryFactory.create('order_return_line_item');
        },

        description(): string {
            return this.$tc('swag-return-management.deleteReturnItemModal.description', this.items.length);
        },
    },

    methods: {
        async onSave(): Promise<void> {
            const deletionPromises = [];
            this.isLoading = true;

            try {
                this.items.forEach(item => {
                    deletionPromises.push(this.orderReturnLineItemRepository.delete(item.id, this.versionContext));
                });

                await Promise.all(deletionPromises);
                await this.orderReturnApiService.recalculateRefundAmount(this.returnId, this.versionContext.versionId);
                this.$emit('item-delete');
            } catch(error) {
                this.createNotificationError({
                    message: error?.response?.data?.errors[0]?.detail,
                });
            } finally {
                this.isLoading = false;
            }
        },

        onCancel(): void {
            this.$emit('modal-close');
        },
    },
});
