/**
 * @package inventory
 */
import template from './sw-product-warehouse-stock.html.twig';
import './sw-product-warehouse-stock.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-product-warehouse-stock', {
    template,
    inject: [
        'acl',
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    props: {
        productId: {
            type: String,
            required: true,
        },
        productVersionId: {
            type: String,
            required: true,
        },
        warehouseGroupId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            warehouses: null,
            isLoading: false,
            sortBy: 'priority',
            sortDirection: 'DESC',
            limit: 10,
            page: 1,
            term: '',
        }
    },
    watch: {
        warehouseGroupId(newValue) {
            if (!newValue) {
                return;
            }

            this.loadWarehouses();
        },
    },
    computed: {
        warehouseRepository() {
            const repository = this.repositoryFactory.create('warehouse_group_warehouse');

            repository.save = this.upsertProductWarehouse.bind(this);

            return repository;
        },
        productWarehouseRepository() {
            return this.repositoryFactory.create('product_warehouse');
        },
        warehouseCriteria() {
            const { productId, warehouseGroupId, term, sortDirection, page, limit } = this;
            const criteria = new Criteria(page, limit);

            criteria.setTerm(term);
            criteria.addFilter(Criteria.equals('warehouseGroupId', warehouseGroupId));

            criteria.addAssociation('warehouse.productWarehouses');
            criteria.getAssociation('warehouse.productWarehouses').addFilter(
                Criteria.equals('productId', productId)
            );

            criteria.addAssociation('warehouseGroup');

            criteria.addSorting(Criteria.sort('priority', sortDirection))

            return criteria;
        },
        warehouseColumns() {
            return [{
                property: 'warehouse.name',
                label: 'sw-product.modal-warehouse-group.columnName',
            }, {
                property: 'warehouse.productWarehouses[0].stock',
                inlineEdit: 'number',
                label: 'sw-product.modal-warehouse-group.columnStock',
                align: 'right',
                sortable: false,
            }, {
                property: 'priority',
                label: 'sw-product.modal-warehouse-group.columnPriority',
                align: 'right',
                sortable: false,
            }];
        },
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.loadWarehouses();
        },
        async loadWarehouses() {
            try {
                this.isLoading = true;

                let warehouses = await this.warehouseRepository.search(this.warehouseCriteria);
                this.createMissingProductWarehouses(warehouses);
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        createMissingProductWarehouses(warehouses) {
            warehouses.forEach((item) => {
                if (item.warehouse.productWarehouses && item.warehouse.productWarehouses.length > 0 ) {
                    return item
                }

                const productWarehouse = this.productWarehouseRepository.create();

                Object.assign(productWarehouse, {
                    warehouseId: item.warehouseId,
                    productId: this.productId,
                    productVersionId: this.productVersionId,
                    stock: 0,
                });

                item.warehouse.productWarehouses.add(productWarehouse);
            })

            this.warehouses = warehouses;
        },
        onChangeSearchTerm(searchTerm) {
            this.term = searchTerm;
            this.page = 1;

            this.loadWarehouses();
        },
        onUpdateRecords(warehouses) {
            this.createMissingProductWarehouses(warehouses);
            this.total = warehouses.total;
            this.limit = warehouses.criteria.limit;
            this.page = warehouses.criteria.page;
        },
        async upsertProductWarehouse(warehouseEntity) {
            const productWarehouse = warehouseEntity.warehouse.productWarehouses[0];

            if (productWarehouse.stock < 0) {
                productWarehouse.stock = 0;
            }

            const response = await this.productWarehouseRepository.save(productWarehouse);

            this.$emit('update-records');

            return response;
        },
    },
});
