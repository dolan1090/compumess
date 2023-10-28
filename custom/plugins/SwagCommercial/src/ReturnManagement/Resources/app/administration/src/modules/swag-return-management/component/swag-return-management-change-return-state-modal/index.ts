import type { PropType } from 'vue';
import template from './swag-return-management-change-return-state-modal.html';

const { Component, State } = Shopware;

interface OrderReturnStateOption {
    disabled: boolean,
    id: string,
    stateName: string,
    name: string
}

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    inject: [
        'orderReturnApiService',
    ],

    props: {
        returnId: {
            type: String,
            required: true,
        },

        selectedState: {
            type: Object as PropType<OrderReturnStateOption>,
            required: true,
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
        versionId(): string {
            return State.get('swOrderDetail').versionContext.versionId;
        },

        description(): string {
            return this.$tc('swag-return-management.changeStateModal.description', 0 , { state: this.selectedState.name });
        },
    },

    methods: {
        async onSave(): Promise<void> {
            this.isLoading = true;

            try {
                await this.orderReturnApiService.changeStateOrderReturn(this.returnId, this.selectedState.id, this.versionId);
                this.$emit('status-change');
            } catch(error) {
                this.createNotificationError({
                    message: this.$tc('swag-return-management.notification.labelErrorStateChange') + error,
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
