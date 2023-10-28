import './sw-customer-employee-card.scss';
import template from './sw-customer-employee-card.html.twig';
import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import type Repository from 'src/core/data/repository.data';

const { Mixin, Component } = Shopware;
const { Criteria } = Shopware.Data;

enum ModalTypes {
    NONE = '',
    DELETE = 'Delete',
    ACTIVATE = 'Activate',
    DEACTIVATE = 'Deactivate',
}

interface SwCustomerEmployeeCardState {
    collection: null | EntityCollection<'b2b_employee'>;
    selectedItem: null | Entity<'b2b_employee'>;
    activeModal: ModalTypes;
    isLoading: boolean;
    isDeleteSuccessful: boolean;
    isActivationSuccessful: boolean;
    sortBy: 'firstName' | 'role' | 'status' | 'email';
    limit: 10 | 25 | 50 | 75 | 100;
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'employeeApiService',
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    props: {
        customer: {
            type: Object as PropType<Entity<'b2b_employee'>>,
            required: true,
        },
    },

    data(): SwCustomerEmployeeCardState {
        return {
            collection: null,
            activeModal: null,
            selectedItem: null,
            isLoading: false,
            isDeleteSuccessful: false,
            isActivationSuccessful: false,
            sortBy: 'firstName',
            limit: 10,
        };
    },

    computed: {
        showListing() {
            const { isLoading, total, term } = this;

            return isLoading || !!total || !!term;
        },

        editMode() {
            if (typeof this.$route.query.edit === 'boolean') {
                return this.$route.query.edit;
            }

            return this.$route.query.edit === 'true';
        },

        employeeRepository(): Repository<'b2b_employee'> {
            return this.repositoryFactory.create('b2b_employee');
        },

        employeeCriteria() {
            const { page, limit, term, sortBy, sortDirection } = this;

            return new Criteria(page, limit)
                .setTerm(term)
                .addSorting(Criteria.sort(sortBy, sortDirection))
                .addFilter(Criteria.equals('businessPartnerCustomerId', this.customer.id))
                .addAssociation('role');
        },

        roleCriteria() {
            return new Criteria().addFilter(Criteria.equals('businessPartnerCustomerId', this.customer.id));
        },

        columns() {
            return [{
                property: 'firstName',
                dataIndex: 'firstName',
                label: this.$tc('sw-customer.employee.labelFirstName'),
                inlineEdit: 'string',
            }, {
                property: 'lastName',
                dataIndex: 'lastName',
                label: this.$tc('sw-customer.employee.labelLastName'),
                inlineEdit: 'string',
            }, {
                property: 'role',
                dataIndex: 'role.name',
                label: this.$tc('sw-customer.employee.labelRole'),
                inlineEdit: 'string',
            }, {
                property: 'status',
                dataIndex: 'status',
                sortable: false,
                label: this.$tc('sw-customer.employee.labelStatus'),
            }, {
                property: 'email',
                dataIndex: 'email',
                label: this.$tc('sw-customer.employee.labelEmail'),
                inlineEdit: 'string',
            }];
        },

        isDeleteDisabled() {
            return !this.acl.can('b2b_employee_management.deleter') || !this.editMode || this.isLoading;
        },

        isEditDisabled() {
            return !this.acl.can('b2b_employee_management.editor') || !this.editMode || this.isLoading;
        },
    },

    methods: {
        deleteModalTitle(recoveryTime: string | null): string {
            return recoveryTime ? this.$tc('sw-customer.employee.titleRevoke') : this.$tc('sw-customer.employee.titleDelete');
        },

        getFullName({ firstName, lastName }: Entity<'b2b_employee'>): string {
            return `${firstName} ${lastName}`;
        },

        invitationAccepted(recoveryTime: string | null): boolean {
            return !recoveryTime;
        },

        async getList() {
            this.isLoading = true;

            try {
                const response = await this.employeeRepository.search(this.employeeCriteria);

                this.onUpdateRecords(response);
            } finally {
                this.isLoading = false;
            }
        },

        onUpdateRecords(collection: EntityCollection<'b2b_employee'>) {
            this.collection = collection;
            this.total = collection.total;
            this.limit = collection.criteria.limit;
            this.page = collection.criteria.page;
        },

        onChangeSearchTerm(searchTerm: string) {
            this.term = searchTerm;
            this.page = 1;

            this.getList();
        },

        isInvitationExpired({ recoveryTime }: Entity<'b2b_employee'>) {
            if (!recoveryTime) {
                return false;
            }

            const tsRecovery = new Date(recoveryTime).getTime();
            const tsValid = new Date().getTime() - 1000 * 60 * 60 * 2;

            return tsRecovery < tsValid;
        },

        onModalClose() {
            this.activeModal = ModalTypes.NONE;
            this.selectedItem = null;

            this.isActivationSuccessful = false;
            this.isDeleteSuccessful = false;
        },

        onCreate() {
            this.$router.push({
                name: 'sw.customer.company.employee.create',
                query: {
                    edit: this.$route.query.edit,
                },
            });
        },

        onDelete(item: Entity<'b2b_employee'>) {
            this.selectedItem = item;
            this.activeModal = ModalTypes.DELETE;
        },

        async onConfirmDelete({ id }: Entity<'b2b_employee'>) {
            this.isLoading = true;
            this.isDeleteSuccessful = false;

            try {
                await this.employeeRepository.delete(id);
                this.isDeleteSuccessful = true;

                this.getList();
            } catch(error) {
                const errorDetail = error.response?.data.errors[0]?.detail;
                const messageSaveError = errorDetail ?? this.$tc(
                    'global.notification.unspecifiedSaveErrorMessage',
                );

                this.createNotificationError({
                    message: messageSaveError,
                });
            } finally {
                this.isLoading = false;
            }
        },

        onToggleActive(item: Entity<'b2b_employee'>) {
            this.selectedItem = item;
            this.activeModal = item.active ? ModalTypes.DEACTIVATE : ModalTypes.ACTIVATE;
        },

        async onConfirmToggleActive(item: Entity<'b2b_employee'>) {
            this.isLoading = true;
            this.isActivationSuccessful = false;
            item.active = !item.active;

            try {
                await this.employeeApiService.updateEmployee(item);
                this.isActivationSuccessful = true;
            } catch(error) {
                const errorDetail = error.response?.data.errors[0]?.detail;
                const messageSaveError = errorDetail ?? this.$tc(
                    'global.notification.unspecifiedSaveErrorMessage',
                );

                this.createNotificationError({
                    message: messageSaveError,
                });
            } finally {
                this.isLoading = false;
                this.onModalClose();
            }
        },

        onEdit(item: Entity<'b2b_employee'>) {
            this.$router.push({
                name: 'sw.customer.company.employee.detail',
                query: {
                    edit: this.$route.query.edit,
                },
                params: {
                    employeeId: item.id,
                },
            });
        },

        onInlineEditSave(promise: Promise<unknown>) {
            promise.catch((error) => {
                if (error.response?.data.errors[0]?.code !== 'B2B__EMPLOYEE_MAIL_NOT_UNIQUE') {
                    return;
                }

                this.createNotificationError({
                    message: this.$tc(
                        'sw-customer.employee.notification.existingEmployeeEmail'
                    ),
                });

              this.$refs.listing.doSearch();
            })
        },

        async onResendInvitation(item: Entity<'b2b_employee'>) {
            this.isLoading = true;

            try {
                await this.employeeApiService.invite(item.id);

                this.createNotificationSuccess({
                    message: this.$tc('sw-customer.employee.notification.inviteSuccessMessage'),
                });

                this.getList();
            } catch(error) {
                this.createNotificationError({
                    message: this.$tc('sw-customer.employee.notification.inviteFailedMessage'),
                });

                this.isLoading = false;
            }
        },
    },
})
