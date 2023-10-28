/**
 * @package buyers-experience
 */
import template from './swag-advanced-search-entity-stream-field-select.html';
import './swag-advanced-search-entity-stream-field-select.scss';

export default {
    template,

    props: {
        definition: {
            type: Object,
            required: true,
        },

        field: {
            type: String,
            required: false,
            default: null,
        },

        index: {
            type: Number,
            required: true,
        },
    },

    computed: {
        options() {
            return Object.keys(this.definition.properties).map((property) => {
                if (property === 'id') {
                    return {
                        label: this.getPropertyTranslation(this.definition.entity, 'id'),
                        value: property,
                    };
                }

                return {
                    label: this.getPropertyTranslation(property),
                    value: property,
                };
            });
        },
    },

    methods: {
        changeField(value: string): void {
            this.$emit('field-changed', { field: value, index: this.index });
        },

        getPropertyTranslation(property: string, fallback = null): string {
            const translationKey = `sw-product-stream.filter.values.${property}`;
            const translated = this.$tc(translationKey);

            return translated === translationKey ? (fallback || property) : translated;
        },
    },
};
