import './sw-customer-role-create.scss';
import template from './sw-customer-role-create.html.twig';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type { PermissionEvent } from '../../component/sw-permission-tree/permission-tree';

const { Component, Mixin } = Shopware;
export interface SwRoleCreateState {
    entity: null | Entity<'b2b_components_role'>;
    isDefaultRole: boolean;
    isLoading: boolean;
    isSaveSuccessful: boolean;
    permissions: PermissionEvent[];
    selectedPermissions: string[];
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'rolePermissionApiService'
    ],

    mixins: [Mixin.getByName('notification')],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    data(): SwRoleCreateState {
        return {
            entity: null,
            isDefaultRole: false,
            isLoading: true,
            isSaveSuccessful: false,
            permissions: [],
            selectedPermissions: [],
        };
    },

    computed: {
        customerId(): string {
            return this.$route.params.id;
        },

        roleRepository() {
            return this.repositoryFactory.create('b2b_components_role');
        },

        companyRepository() {
            return this.repositoryFactory.create('b2b_business_partner');
        },

        customerRepository() {
            return this.repositoryFactory.create('customer');
        },

        errorName() {
            return this.$store.getters['error/getApiError'](this.entity, 'name');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initializeEntity();
            this.loadPermissions();
        },

        initializeEntity() {
            const entity = this.roleRepository.create();
            entity.businessPartnerCustomerId = this.customerId;
            entity.name = '';
            this.entity = entity;
        },

        async loadPermissions() {
            this.isLoading = true;

            try {
                const response = await this.rolePermissionApiService.getAllPermissions();
                this.permissions = response.data;
            } catch {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.permissionsNotFound',
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onSave() {
            this.isLoading = true;

            try {
                await this.saveRole();
                this.isSaveSuccessful = true;
            } catch (error) {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.saveError',
                });
            } finally {
                this.isLoading = false;
            }
        },

        async saveRole() {
            this.entity.permissions = this.selectedPermissions;
            await this.roleRepository.save(this.entity);
            await this.saveDefaultRole();
        },

        async saveDefaultRole() {
            const company = await this.getCompany();

            if (!company) {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.companyNotFound',
                });

                return;
            }

            if (!this.shouldChangeDefaultRole(company)) {
                return;
            }

            company.defaultRoleId = this.isDefaultRole ? this.entity.id : null;

            try {
                await this.companyRepository.save(company, Shopware.Context.api);
            } catch {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.setDefaultRoleError',
                });
            }
        },

        shouldChangeDefaultRole(company: Entity<'b2b_company'>) {
            if (this.entity.isNew() && this.isDefaultRole) {
                return true;
            }

            if (!this.entity.isNew() && this.isDefaultRole) {
                return company.defaultRoleId !== this.entity.id;
            }

            if (!this.entity.isNew()) {
                return company.defaultRoleId === this.entity.id;
            }

            return false;
        },

        onChangePermissions(permissions: string[]) {
            this.selectedPermissions = permissions;
        },

        onCancel() {
            this.navigateToCompany();
        },

        onSaveFinish() {
            this.navigateToDetailPage();
        },

        async getCompany() {
            const customer = await this.customerRepository.get(this.customerId, Shopware.Context.api);

            if (!customer) {
                return null;
            }

            return customer.extensions?.b2bBusinessPartner
        },

        navigateToDetailPage() {
            this.$router.push({
                name: 'sw.customer.company.role.detail',
                params: {
                    roleId: this.entity.id,
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
    },
});
