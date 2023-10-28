/**
 * @package checkout
 */

import template from './swag-customer-classification-success-modal.html';

const { Component } = Shopware;

export default Component.wrapComponentConfig({
    template,

    props: {
        itemTotal: {
            required: true,
            type: Number,
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.updateButtons();
            this.setTitle();
        },

        setTitle(): void {
            this.$emit('title-set', this.$tc('swag-customer-classification.notificationModal.success.title'));
        },

        updateButtons(): void {
            const buttonConfig = [
                {
                    key: 'cancel',
                    label: this.$tc('global.sw-modal.labelClose'),
                    position: 'right',
                    action: '',
                    disabled: false,
                },
            ];

            this.$emit('buttons-update', buttonConfig);
        },
    },
});
