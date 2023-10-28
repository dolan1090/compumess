/**
 * @package inventory
 */
import template from './sw-settings-warehouse-group-list.html.twig';
import './sw-settings-warehouse-group-list.scss';
import licenseDecorator from '../../../../core/helper/license-decorator.helper';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-settings-warehouse-group-list', {
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
            warehouseGroups: null,
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
            return licenseDecorator(this.repositoryFactory.create('warehouse_group'), 'MULTI_INVENTORY-2506228');
        },
        columns() {
            return [{
                property: 'name',
                label: 'sw-settings-warehouse-group.list.columnName',
                routerLink: 'sw.settings.warehouse.group.detail',
                inlineEdit: 'string',
                primary: true,
            }, {
                property: 'rule.name',
                label: 'sw-settings-warehouse-group.list.columnRule'
            }, {
                property: 'priority',
                label: 'sw-settings-warehouse-group.list.columnPriority',
                inlineEdit: 'number',
            }];
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

            return new Criteria(page, limit)
                .setTerm(term)
                .addSorting(Criteria.sort(sortBy, sortDirection, naturalSorting))
                .addAssociation('rule');
        },
        async getList() {
            try {
                this.isLoading = true;
                this.naturalSorting = this.sortBy === 'name';

                this.warehouseGroups = await this.repository.search(this.getCriteria());

                this.total = this.warehouseGroups ? this.warehouseGroups.total : 0;
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
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
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
                });
            } finally {
                await this.getList();
            }
        },
        onUpdateRecords(warehouseGroups) {
            this.warehouseGroups = warehouseGroups;
            this.total = warehouseGroups.total;
            this.limit = warehouseGroups.criteria.limit;
            this.page = warehouseGroups.criteria.page;
        },
    }
});
