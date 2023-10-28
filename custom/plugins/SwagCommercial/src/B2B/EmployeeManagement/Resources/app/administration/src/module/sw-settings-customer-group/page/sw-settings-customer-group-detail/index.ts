import template from './sw-settings-customer-group-detail.html.twig';

export default {
    template,

    watch: {
        customerGroup: {
            handler() {
                if (!this.customerGroup) {
                    return; // customerGroup isn't initialized yet
                }

                if (!this.customerGroup.customFields) {
                    this.$set(this.customerGroup, 'customFields', {})
                }
            }
        },
    },

    computed: {
        anyFeatureToggleActive() {
            const flags = ['employee_management_activate_feature', 'swag_quick_order_activate_feature'];

            return flags.some(flag => {
                return this.customerGroup.customFields[flag];
            });
        }
    },

    methods: {
        onChangeSwagActivateFeature(value) {
            if (Shopware.License.get('EMPLOYEE_MANAGEMENT-1484282') || Shopware.License.get('QUICK_ORDER-3339699')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get('api/_info/config', {
                    headers: {
                        Accept: 'application/vnd.api+json',
                        Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                        'Content-Type': 'application/json',
                        'sw-license-toggle': 'EMPLOYEE_MANAGEMENT-1484282',
                    },
                });
                return;
            }

            if (value) {
                this.$set(this.customerGroup, 'registrationOnlyCompanyRegistration', true);
            }
        },
    }
};
