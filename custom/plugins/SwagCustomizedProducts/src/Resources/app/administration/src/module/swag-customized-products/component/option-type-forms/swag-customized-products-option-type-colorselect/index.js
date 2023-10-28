import template from './swag-customized-products-option-type-colorselect.html.twig';

const { Component } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.extend('swag-customized-products-option-type-colorselect', 'swag-customized-products-option-type-base-tree', {
    template,

    computed: {
        ...mapPropertyErrors('activeItem', ['value._value']),
    },
});
