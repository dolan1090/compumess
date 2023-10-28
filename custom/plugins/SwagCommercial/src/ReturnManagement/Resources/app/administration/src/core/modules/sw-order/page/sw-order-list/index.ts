import { TOGGLE_KEY } from '../../../../../config';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    computed: {
        listFilterOptions() {
            if (this.acl.can('order_return.viewer')
                && Shopware.License.get(TOGGLE_KEY)) {
                return {
                    ...this.$super('listFilterOptions'),
                    'returns-filter': {
                        property: 'returns.state',
                        label: this.$tc('swag-return-management.returnFilter.label'),
                        placeholder: this.$tc('swag-return-management.returnFilter.placeholder'),
                        criteria: this.getStatusCriteria('order_return.state'),
                    },
                };
            }

            return this.$super('listFilterOptions');
        },
    },

    methods: {
        createdComponent(): void {
            if (this.acl.can('order_return.viewer')
                && Shopware.License.get(TOGGLE_KEY)) {
                this.defaultFilters = [
                    ...this.defaultFilters,
                    'returns-filter',
                ];
            }

            this.$super('createdComponent');
        },
    }
});
