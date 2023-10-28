import template from './swag-customized-products-option-tree-default-select.html.twig';

const { Component } = Shopware;

Component.register('swag-customized-products-option-tree-default-select', {
    template,

    props: {
        option: {
            type: Object,
            required: true,
        },

        optionValues: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            defaultSingleValue: null,
            defaultMultiValue: [],
        };
    },

    computed: {
        defaultValueOptions() {
            return Array.from(this.optionValues).sort(({ position: pos1 }, { position: pos2 }) => pos1 - pos2);
        },
    },

    watch: {
        'option.typeProperties.isMultiSelect'() {
            this.onChangeDefault(null);
        },

        optionValues() {
            this.defaultMultiValue = [];
            this.defaultSingleValue = null;

            this.optionValues.forEach((optionValue) => {
                if (optionValue.default) {
                    if (this.option.typeProperties.isMultiSelect) {
                        this.defaultMultiValue.push(optionValue.id);
                    } else {
                        this.defaultSingleValue = optionValue.id;
                    }
                }
            });
        },
    },

    methods: {
        onChangeDefault(itemId = null) {
            this.optionValues.forEach(optionValue => {
                if (itemId === null) {
                    optionValue.default = false;
                    return;
                }

                if (this.option.typeProperties.isMultiSelect) {
                    optionValue.default = itemId.includes(optionValue.id);
                } else {
                    optionValue.default = itemId === optionValue.id;
                }
            });
        },
    },
});
