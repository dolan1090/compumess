import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/core/data/repository.data';

import template from './swag-return-management-delete-return-modal.html';
import './swag-return-management-delete-return-modal.scss';

const { Component, Mixin } = Shopware;
const { mapState } = Component.getComponentHelper();

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        orderReturn: {
            type: Object as PropType<Entity<'order_return'>>,
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
            'versionContext'
        ]),

        orderReturnRepository(): RepositoryType<'order_return'> {
            return this.repositoryFactory.create('order_return');
        },

        modalTitle(): string {
            return this.$tc('swag-return-management.deleteReturnModal.confirmation', 0,
                { returnNumber: this.orderReturn.returnNumber });
        },
    },

    methods: {
        async onSave(): Promise<void> {
            this.isLoading = true;

            try {
                await this.orderReturnRepository.delete(this.orderReturn.id, this.versionContext);
                this.$emit('reload-data');
                this.onCancel();
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
