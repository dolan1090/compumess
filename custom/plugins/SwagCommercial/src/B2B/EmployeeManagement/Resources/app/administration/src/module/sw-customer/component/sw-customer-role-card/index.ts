import './sw-customer-role-card.scss';
import template from './sw-customer-role-card.html.twig';
import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';

const { Mixin, Component } = Shopware;
const { Criteria } = Shopware.Data;

interface SwCustomerRoleCardState {
    collection: null | EntityCollection<'b2b_components_role'>;
    selectedItem: null | Entity<'b2b_components_role'>;
    isLoading: boolean;
    isDeleteSuccessful: boolean;
    showDeleteModal: boolean;
    limit: 10 | 25 | 50 | 75 | 100;
}

export default Component.wrapComponentConfig({
    template,

    inject: [
        'acl',
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    props: {
        customer: {
            type: Object as PropType<Entity<'b2b_customer'>>,
            required: true,
        },
    },

    data(): SwCustomerRoleCardState {
        return {
            collection: null,
            selectedItem: null,
            isLoading: false,
            isDeleteSuccessful: false,
            showDeleteModal: false,
            limit: 10,
        };
    },

    computed: {
        roleRepository() {
            return this.repositoryFactory.create('b2b_components_role');
        },

        roleCriteria() {
            const { page, limit, term } = this;

            return new Criteria(page, limit)
                .setTerm(term)
                .addFilter(Criteria.equals('businessPartnerCustomerId', this.customer.id));
        },

        columns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('sw-customer.role.list.grid.columnRole'),
                    inlineEdit: 'string',
                }
            ];
        },

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

        creationDisabled() {
            return  !this.editMode || this.isLoading || !this.acl.can('b2b_employee_management.creator');
        },

        deletionDisabled() {
            return  !this.editMode || this.isLoading || !this.acl.can('b2b_employee_management.deleter');
        },
    },

    methods: {
        async getList() {
            this.isLoading = true;

            try {
                const response = await this.roleRepository.search(this.roleCriteria, Shopware.Context.api);
                this.updateCollection(response);
            } finally {
                this.isLoading = false;
            }
        },

        async onConfirmDelete(id: string) {
            this.isLoading = true;
            this.isDeleteSuccessful = false;

            try {
                await this.roleRepository.delete(id);
                this.isDeleteSuccessful = true;

                this.getList();
            } catch {
                this.createNotificationError({
                    message: 'sw-customer.role.notification.deleteError',
                });
            } finally {
                this.isLoading = false;
            }
        },

        updateCollection(collection: EntityCollection<'b2b_components_role'>) {
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

        onModalClose() {
            this.showDeleteModal = false;
            this.isDeleteSuccessful = false;
            this.selectedItem = null;
        },

        onDelete(item: Entity<'b2b_components_role'>) {
            this.selectedItem = item;
            this.showDeleteModal = true;
        },

        onEdit(item: Entity<'b2b_components_role'>) {
            this.$router.push({
                name: 'sw.customer.company.role.detail',
                query: {
                    edit: this.$route.query.edit,
                },
                params: {
                    roleId: item.id,
                },
            });
        },

        onCreate() {
            this.$router.push({
                name: 'sw.customer.company.role.create',
                query: {
                    edit: this.$route.query.edit,
                },
            });
        },
    }
});
