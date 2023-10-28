import template from './swag-customized-products-option-type-timestamp.html.twig';

const { Component } = Shopware;

Component.extend('swag-customized-products-option-type-timestamp', 'swag-customized-products-option-type-base', {
    template,

    inject: ['feature'],

    data() {
        return {
            maxTimeConfig: {
                minTime: null,
            },
        };
    },

    watch: {
        'option.typeProperties.startTime'(value) {
            this.onStartTimeChanged(value);
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
            this.initializeStartTime();
        },

        initializeStartTime() {
            this.onStartTimeChanged(this.option.typeProperties.startTime, true);
        },

        onStartTimeChanged(value, initial = false) {
            if (!value) {
                this.disableMaxTimeField();
                return;
            }

            this.enableMaxTimeField();

            if (initial) {
                return;
            }
            const maxTimeField = this.$refs.maxTimeField;
            maxTimeField.flatpickrInstance.setDate(value);
        },

        enableMaxTimeField() {
            const maxTimeField = this.$refs.maxTimeField;

            if (this.acl.can('swag_customized_products_template.editor')) {
                maxTimeField.flatpickrInstance._input.removeAttribute('disabled');
            }

            this.maxTimeConfig.minTime = this.option.typeProperties.startTime;
        },

        disableMaxTimeField() {
            const maxTimeField = this.$refs.maxTimeField;
            maxTimeField.flatpickrInstance._input.setAttribute('disabled', 'disabled');
            maxTimeField.flatpickrInstance.clear();
        },
    },
});
