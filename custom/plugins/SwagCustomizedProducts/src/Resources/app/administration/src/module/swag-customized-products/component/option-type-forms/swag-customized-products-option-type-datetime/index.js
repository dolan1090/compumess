import template from './swag-customized-products-option-type-datetime.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-datetime', 'swag-customized-products-option-type-base', {
    template,

    inject: ['feature'],

    data() {
        return {
            minSelectableDate: '0000-01-01',
            minDateConfig: {
                defaultDate: new Date(),
            },
            maxDateConfig: {
                disable: [],
            },
        };
    },

    watch: {
        'option.typeProperties.minDate'(value) {
            this.onMinDateChange(value);
        },
    },

    mounted() {
        if (!this.feature.isActive('VUE3')) {
            this.mountedComponent();
        }
    },

    methods: {
        /** @deprecated tag:v6.6.0 - when VUE3 is running, will be removed without replacement */
        mountedComponent() {
            this.initializeMinDate()
        },

        initializeMinDate() {
            this.maxDateConfig.defaultDate = this.option.typeProperties.minDate;
            this.onMinDateChange(this.option.typeProperties.minDate, true);
        },

        onMinDateChange(value, initial = false) {
            if (!value) {
                this.disableMaxDateField();
                return;
            }

            this.enableMaxDateField();

            if (initial) {
                return;
            }

            const maxDateField = this.$refs.maxDateField;
            maxDateField.flatpickrInstance.clear();
        },

        enableMaxDateField() {
            const maxDateField = this.$refs.maxDateField;

            if (this.acl.can('swag_customized_products_template.editor')) {
                maxDateField.flatpickrInstance._input.removeAttribute('disabled');
            }

            const newToDate = new Date(this.option.typeProperties.minDate);

            // We have to subtract 1 day to allow selecting the current day
            newToDate.setDate(newToDate.getDate() - 1);
            this.maxDateConfig.disable = [{
                from: new Date(this.minSelectableDate),
                to: newToDate,
            }];
        },

        disableMaxDateField() {
            const maxDateField = this.$refs.maxDateField;
            maxDateField.flatpickrInstance._input.setAttribute('disabled', 'disabled');
            maxDateField.flatpickrInstance.clear();
        },
    },
});
