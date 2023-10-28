import template from './swag-customized-products-option-type-header.html.twig';
import './swag-customized-products-option-type-header.scss';

const { Component } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('swag-customized-products-option-type-header', {
    template,

    inject: [
        'acl',
    ],

    mixins: [
        'placeholder',
    ],

    props: {
        option: {
            type: Object,
            required: true,
        },

        showRequired: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line
            default: true,
        },

        data: {
            type: Object,
            required: false,
            default: null,
        },
    },

    computed: {
        displayName: {
            get() {
                if (this.data) {
                    return this.data.name;
                }
                return this.option.displayName;
            },

            set(newName) {
                if (this.data) {
                    this.data.name = newName;
                }
                this.option.displayName = newName;
            },
        },
        requiredTooltip() {
            if (this.option.templateExclusionConditions.length > 0) {
                return this.$tc('swag-customized-products.optionDetailModal.optionType.requiredTooltip.disabled');
            }

            return this.$tc('swag-customized-products.optionDetailModal.optionType.requiredTooltip.enabled');
        },

        ...mapPropertyErrors('option', ['displayName']),
    },
});
