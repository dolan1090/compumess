import './sw-customer-employee-detail.scss';
import template from './sw-customer-employee-detail.html.twig';
// The index suffix is needed to load the interface correctly
import type { SwEmployeeCreateState } from '../sw-customer-employee-create/index';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

const { Criteria } = Shopware.Data;

interface SwEmployeeDetailState extends SwEmployeeCreateState {
    isLoadingDelete: boolean;
    showDeleteModal: boolean;
    isDeleteSuccessful: boolean;
}

export default Shopware.Component.wrapComponentConfig({
    template,

    data(): SwEmployeeDetailState {
        return {
            entity: null,
            isLoading: true,
            isSaveSuccessful: false,
            isLoadingDelete: false,
            isDeleteSuccessful: false,
            showDeleteModal: false,
            defaultRoleId: null,
        };
    },

    computed: {
        employeeId() {
            return this.$route.params.employeeId;
        },
    },

    methods: {
        async initEntity() {
            this.entity = await this.employeeRepository.get(this.employeeId);
            this.isLoading = false;
        },

        async save() {
            this.isLoading = true;
            this.isSaveSuccessful = false;

            try {
                await this.employeeApiService.updateEmployee(this.entity);
                this.isSaveSuccessful = true;
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

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        async onConfirmDelete() {
            this.isLoadingDelete = true;
            this.isDeleteSuccessful = false;
            this.onCloseDeleteModal();

            try {
                await this.employeeRepository.delete(this.employeeId);
                this.isDeleteSuccessful = true;
                this.onDeleteFinish();
            } finally {
                this.isLoadingDelete = false;
            }
        },

        onDeleteFinish() {
            this.isDeleteSuccessful = false;
            this.navigateToCompany();
        },

        getFullName() {
            const { firstName, lastName } = this.entity;

            return `${firstName} ${lastName}`;
        }
    },
});
