/**
 * @package checkout
 */

import template from './swag-customer-classification-error-modal.html';

const { Component } = Shopware;

export default Component.wrapComponentConfig({
    template,

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.updateButtons();
            this.setTitle();
        },

        setTitle(): void {
            this.$emit('title-set', this.$tc('swag-customer-classification.notificationModal.error.title'));
        },

        updateButtons(): void {
            const buttonConfig = [
                {
                    key: 'close',
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
