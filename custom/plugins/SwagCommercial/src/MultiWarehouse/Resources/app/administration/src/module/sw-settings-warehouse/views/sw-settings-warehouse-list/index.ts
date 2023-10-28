/**
 * @package inventory
 */
import template from './sw-settings-warehouse-list.html.twig';
import './sw-settings-warehouse-list.scss';
import licenseDecorator from '../../../../core/helper/license-decorator.helper';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-settings-warehouse-list', {
    template,
    inject: [
        'acl',
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],
    data() {
        return {
            warehouses: null,
            isLoading: false,
            showDeleteModal: false,
            sortBy: 'name',
            limit: 10,
        };
    },
    computed: {
        showListing() {
            const { isLoading, total, term } = this;

            return isLoading || total || term;
        },
        repository() {
            return licenseDecorator(this.repositoryFactory.create('warehouse'), 'MULTI_INVENTORY-2131206');
        },
        columns() {
            const columns: any[] = [{
                property: 'name',
                label: 'sw-settings-warehouse.list.columnName',
                routerLink: 'sw.settings.warehouse.detail',
                inlineEdit: 'string',
                primary: true,
            }];

            if (this.acl.can('warehouse-group.viewer')) {
                columns.push({
                    property: 'group',
                    label: 'sw-settings-warehouse.list.columnWarehouseGroups',
                    sortable: false,
                });
            }

            return columns;
        },
    },
    methods: {
        onChangeSearchTerm(searchTerm) {
            this.term = searchTerm;
            this.page = 1;

            this.getList();
        },
        onClickDelete(id) {
            this.showDeleteModal = id;
        },
        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },
        getCriteria() {
            const { page, limit, term, sortBy, sortDirection, naturalSorting } = this;

            const criteria = new Criteria(page, limit)
                .setTerm(term)
                .addSorting(Criteria.sort(sortBy, sortDirection, naturalSorting));

            if (this.acl.can('warehouse-group.viewer')) {
                criteria.addAssociation('groups');
            }

            return criteria;
        },
        async getList() {
            try {
                this.isLoading = true;
                this.naturalSorting = this.sortBy === 'name';

                this.warehouses = await this.repository.search(this.getCriteria());

                this.total = this.warehouses ? this.warehouses.total : 0;
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        async onConfirmDelete(id) {
            try {
                this.isLoading = true;
                this.showDeleteModal = false;

                await this.repository.delete(id);
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                await this.getList();
            }
        },
        onUpdateRecords(warehouses) {
            this.warehouses = warehouses;
            this.total = warehouses.total;
            this.limit = warehouses.criteria.limit;
            this.page = warehouses.criteria.page;
        },
    },
});
