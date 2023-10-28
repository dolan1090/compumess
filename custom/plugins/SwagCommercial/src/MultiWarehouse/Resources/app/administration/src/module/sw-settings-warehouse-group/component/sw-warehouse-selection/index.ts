/**
 * @package inventory
 */
import template from './sw-warehouse-selection.html.twig';
import './sw-warehouse-selection.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-warehouse-selection', {
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
        warehouseGroupId: {
            type: String,
            required: true,
        },
        initialSelection: {
            type: Array,
            default: () => ([]),
        },
    },
    data() {
        return {
            isLoading: false,
            selection: [],
            warehouses: null,
            page: 1,
            limit: 10,
            term: '',
        }
    },
    computed: {
        showListing() {
            const { isLoading, total, term } = this;

            return isLoading || total || term;
        },
        repository() {
            return this.repositoryFactory.create('warehouse');
        },
        columns() {
            return [{
                property: 'name',
                label: 'sw-settings-warehouse-group.warehouseAssignment.columnWarehouseName',
                primary: true,
            }];
        },
        criteria() {
            const { page, limit, term } = this;

            const criteria = new Criteria(page, limit)
                .setTerm(term)
                .addAssociation('groups')
                .addFilter(Criteria.not('and', [Criteria.equals('groups.id', this.warehouseGroupId)]))
                .addAssociation('productWarehouses');

            if (this.initialSelection.length) {
                criteria.addFilter(Criteria.not('and', [
                    Criteria.equalsAny('id', this.initialSelection),
                ]));
            }

            return criteria;
        },
    },
    methods: {
        onChangeSearchTerm(term) {
            this.term = term;
            this.page = 1;

            this.getList();
        },
        async getList() {
            try {
                this.isLoading = true;

                this.warehouses = await this.repository.search(this.criteria);

                this.total = this.warehouses ? this.warehouses.total : 0;
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        onUpdateRecords(warehouses) {
            this.warehouses = warehouses;
            this.total = warehouses.total;
            this.limit = warehouses.criteria.limit;
            this.page = warehouses.criteria.page;
        },
        onSelectionChange(selection = {}) {
            this.selection = Array.from(Object.values(selection));
        },
        onCancel() {
            this.$emit('modal-close');
        },
        onConfirmSelection() {
            this.$emit('confirm-selection', this.selection);
        },
    },
});
