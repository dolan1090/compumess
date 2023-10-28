import './sw-customer-role-detail.scss';
import template from './sw-customer-role-detail.html.twig';
import { SwRoleCreateState } from "../sw-customer-role-create";

const { Component } = Shopware;

interface SwRoleDetailState extends SwRoleCreateState {
    isLoadingDelete: boolean;
    showDeleteModal: boolean;
    isDeleteSuccessful: boolean;
}

export default Component.wrapComponentConfig({
    template,

    data(): SwRoleDetailState {
        return {
            entity: null,
            isDefaultRole: false,
            isLoading: true,
            isSaveSuccessful: false,
            permissions: [],
            selectedPermissions: [],
            isLoadingDelete: false,
            isDeleteSuccessful: false,
            showDeleteModal: false,
        } as SwRoleDetailState;
    },

    computed: {
        roleId() {
            return this.$route.params.roleId;
        },
    },

    methods: {
        async initializeEntity() {
            this.entity = await this.roleRepository.get(this.roleId);
            this.selectedPermissions = this.entity.permissions;

            const company = await this.getCompany();

            if (company?.defaultRoleId === this.entity.id) {
                this.isDefaultRole = true;
            }

            this.isLoading = false;
        },

        async onConfirmDelete() {
            this.isLoading = true;
            this.isDeleteSuccessful = false;

            try {
                await this.roleRepository.delete(this.roleId);
                this.isDeleteSuccessful = true;
            } catch {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.deleteError',
                });
            } finally {
                this.isLoading = false;
            }
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onDeleteFinish() {
            this.navigateToCompany();
            this.showDeleteModal = false;
            this.isDeleteSuccessful = false;
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
        },
    },
});

