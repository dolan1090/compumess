/**
 * @package checkout
 */

import template from './swag-customer-classification-confirm-modal.html';
import './swag-customer-classification-confirm-modal.scss';

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
            this.$emit('title-set', this.$tc('swag-customer-classification.notificationModal.confirm.title'));
        },

        updateButtons(): void {
            const buttonConfig = [
                {
                    key: 'cancel',
                    label: this.$tc('global.sw-modal.labelClose'),
                    position: 'left',
                    action: '',
                    disabled: false,
                },
                {
                    key: 'next',
                    label: this.$tc('swag-customer-classification.notificationModal.confirm.buttonContinue'),
                    position: 'right',
                    variant: 'primary',
                    action: 'process',
                    disabled: false,
                },
            ];

            this.$emit('buttons-update', buttonConfig);
        },
    },
});
