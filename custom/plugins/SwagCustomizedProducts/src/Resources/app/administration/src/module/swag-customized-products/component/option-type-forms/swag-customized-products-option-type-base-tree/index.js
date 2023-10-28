import template from './swag-customized-products-type-base-tree.html.twig';

const { Component } = Shopware;
const { types } = Shopware.Utils;

Component.register('swag-customized-products-option-type-base-tree', {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    props: {
        option: {
            type: Object,
            required: true,
        },

        versionContext: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            activeItem: null,
            defaultValue: null,
            optionValues: [],
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.registerWatcher();

            const previousDefault = this.option.values.find(value => value.default);
            if (previousDefault) {
                this.defaultValue = previousDefault.id;
            }

            if (!this.checkRequired(this.option.percentageSurcharge)) {
                this.option.percentageSurcharge = 0;
            }

            if (this.option.typeProperties.isMultiSelect !== undefined) {
                return;
            }

            this.$set(this.option.typeProperties, 'isMultiSelect', false);
        },

        registerWatcher() {
            this.$watch('activeItem', (value) => {
                this.$emit('option-valid', this.validateInput(value));
            }, {
                deep: true,
                immediate: true,
            });
            return true;
        },

        validateInput(value) {
            return value !== null;
        },

        checkRequired(value) {
            return !types.isUndefined(value) && (
                types.isNumber(value) ||
                (types.isString(value) && value.length > 0)
            );
        },

        setActiveItem(item) {
            this.activeItem = item;
        },

        setOptionValues(data) {
            this.optionValues = data;
        },
    },
});
