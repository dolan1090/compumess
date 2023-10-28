/**
 * @package buyers-experience
 */
import template from './sw-settings-search-searchable-content-general.html.twig';

export default {
    template,

    methods: {
        getMatchingFields(fieldName) {
            if (!fieldName) {
                return '';
            }

            const fieldItem = this.fieldConfigs.find(fieldConfig => fieldConfig.value === fieldName);

            return fieldItem
                ? fieldItem.label
                : this.$tc(`swag-advanced-search.advancedSearchConfigField.product.${fieldName}`);
        },
    },
};
