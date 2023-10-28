import template from './sw-customer-detail.html.twig';

const { Component, License } = Shopware;

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
    ],

    computed: {
        b2bBusinessPartnerRepository() {
            return this.repositoryFactory.create('b2b_business_partner');
        },

        employeeManagementActive() {
            return !!this.customer?.extensions?.specificFeatures?.features?.EMPLOYEE_MANAGEMENT;
        },

        employeeRoute() {
            return {
                name: 'sw.customer.detail.company',
                params: { id: this.customerId },
                query: { edit: this.editMode },
            };
        },

        isCompanyPageActive() {
            if (!this.acl.can('b2b_employee_management.viewer') || !this.customer || !this.customer.extensions) {
                return false;
            }

            const { b2bBusinessPartner } = this.customer.extensions;

            if (!b2bBusinessPartner) {
                return false;
            }

            return (
                !b2bBusinessPartner._isNew &&
                !b2bBusinessPartner.isDeleted
            );
        },
    },

    methods: {
        async onSave() {
            if (License.get('EMPLOYEE_MANAGEMENT-7001194')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get('api/_info/config', {
                    headers: {
                        Accept: 'application/vnd.api+json',
                        Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                        'Content-Type': 'application/json',
                        'sw-license-toggle': 'EMPLOYEE_MANAGEMENT-7001194',
                    },
                });
                return;
            }

            if (this.employeeManagementActive) {
                this.createB2bBusinessPartner();
            } else {
                await this.deleteB2bBusinessPartner();
            }

            await this.$super('onSave');
        },

        createB2bBusinessPartner() {
            if (this.customer.extensions?.b2bBusinessPartner) {
                return;
            }

            const newBusinessPartner = this.b2bBusinessPartnerRepository.create();
            newBusinessPartner.customerId = this.customer.id;
            this.customer.extensions.b2bBusinessPartner = newBusinessPartner;
        },

        async deleteB2bBusinessPartner() {
            if (!this.customer.extensions.b2bBusinessPartner) {
                return;
            }

            try {
                await this.b2bBusinessPartnerRepository.delete(this.customer.extensions.b2bBusinessPartner.id);
            } catch (error) {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.unspecifiedSaveErrorMessage',
                    ),
                });
            }
        },
    },
});
