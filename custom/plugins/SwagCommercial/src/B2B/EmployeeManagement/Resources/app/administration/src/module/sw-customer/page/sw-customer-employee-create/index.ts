import template from './sw-customer-employee-create.html.twig';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export interface SwEmployeeCreateState {
    entity: null | Entity<'b2b_employee'>;
    isLoading: boolean;
    isSaveSuccessful: boolean;
    defaultRoleId: null | string;
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'employeeApiService',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    data(): SwEmployeeCreateState {
        return {
            entity: null,
            isLoading: true,
            isSaveSuccessful: false,
            defaultRoleId: null,
        }
    },

    computed: {
        customerId() {
            return this.$route.params.id;
        },

        customerRepository() {
            return this.repositoryFactory.create('customer');
        },

        employeeRepository() {
            return this.repositoryFactory.create('b2b_employee');
        },

        businessPartnerRepository() {
            return this.repositoryFactory.create('b2b_business_partner');
        },

        roleCriteria() {
            return new Criteria().addFilter(Criteria.equals('businessPartnerCustomerId', this.customerId));
        },

        businessPartnerCriteria() {
            return new Criteria().addFilter(Criteria.equals('customerId', this.customerId));
        },

        errorFirstName() {
            return this.$store.getters['error/getApiError'](this.entity, 'firstName');
        },

        errorLastName() {
            return this.$store.getters['error/getApiError'](this.entity, 'lastName');
        },

        errorEmail() {
            return this.$store.getters['error/getApiError'](this.entity, 'email');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            await this.initDefaultRole();
            this.initEntity();
        },

        async onSave() {
            await this.save();
        },

        async save() {
            this.isLoading = true;
            this.isSaveSuccessful = false;

            try {
                const response = await this.employeeApiService.createEmployee(this.entity);
                this.isSaveSuccessful = true;
                this.entity.id = response.data[0];

                await this.sendInvitation();
                this.navigateToDetailPage();
            } catch (error) {
                const errorDetail = error.response?.data.errors[0]?.detail;
                let messageSaveError = errorDetail ?? this.$tc(
                    'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                );

                if (error.response?.data.errors[0]?.code === 'B2B__EMPLOYEE_MAIL_NOT_UNIQUE') {
                    messageSaveError = this.$tc('sw-customer.employee.notification.existingEmployeeEmail');
                }

                this.createNotificationError({
                    message: messageSaveError,
                });
            } finally {
                this.isLoading = false;
            }
        },

        async sendInvitation() {
            this.isLoading = true;

            try {
                await this.employeeApiService.invite(this.entity.id);

                this.createNotificationSuccess({
                    message: this.$tc('sw-customer.employee.notification.inviteSuccessMessage'),
                });
            } catch(error) {
                this.createNotificationError({
                    message: this.$tc('sw-customer.employee.notification.inviteFailedMessage'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        onCancel() {
            this.navigateToCompany();
        },

        initEntity() {
            const entity = this.employeeRepository.create();
            entity.businessPartnerCustomerId = this.customerId;
            entity.roleId = this.defaultRoleId;
            this.entity = entity;
            this.isLoading = false;
        },

        navigateToDetailPage() {
            this.$router.push({
                name: 'sw.customer.company.employee.detail',
                params: {
                    employeeId: this.entity.id,
                },
            });
        },

        navigateToCompany() {
            this.$router.push({
                name: 'sw.customer.detail.company',
                query: {
                    edit: false,
                },
            });
        },

        async initDefaultRole() {
            const businessPartner = (await this.businessPartnerRepository.search(this.businessPartnerCriteria)).first();

            if (!businessPartner || !businessPartner.defaultRoleId) {
                return;
            }

            this.defaultRoleId = businessPartner.defaultRoleId;
        },
    },
});
