const { Component } = Shopware;

Component.extend('swag-customized-products-nullable-number-field', 'sw-number-field', {
    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        computeValue(stringRepresentation) {
            if (stringRepresentation === '') {
                this.currentValue = null;
                return;
            }

            const value = this.getNumberFromString(stringRepresentation);
            this.currentValue = this.parseValue(value);
        },
    },
});
