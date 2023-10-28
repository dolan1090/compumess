/**
 * @package checkout
 */

import template from './swag-customer-classification-process-modal.html';

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
            this.$emit('start-classify');
        },

        setTitle(): void {
            this.$emit('title-set', this.$tc('swag-customer-classification.notificationModal.process.title'));
        },

        updateButtons(): void {
            const buttonConfig = [
                {
                    key: 'next',
                    label: this.$tc('global.sw-modal.labelClose'),
                    position: 'right',
                    action: '',
                    disabled: true,
                },
            ];

            this.$emit('buttons-update', buttonConfig);
        },
    },
});
