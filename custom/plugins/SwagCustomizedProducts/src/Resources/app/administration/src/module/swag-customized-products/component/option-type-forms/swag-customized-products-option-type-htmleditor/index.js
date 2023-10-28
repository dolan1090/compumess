import template from './swag-customized-products-option-type-htmleditor.html.twig';

const { Component } = Shopware;
const { isArray } = Shopware.Utils.types;

Component.extend('swag-customized-products-option-type-htmleditor', 'swag-customized-products-option-type-base', {
    template,

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            if (isArray(this.option.typeProperties)) {
                this.$set(this.option, 'typeProperties', {});
            }

            if (this.option.typeProperties.hasOwnProperty('placeholder')) {
                return;
            }

            this.option.typeProperties.placeholder = '';
        },
    },
});
