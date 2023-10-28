/**
 * @package inventory
 */
import template from './sw-product-warehouse-group-modal.html.twig';
import './sw-product-warehouse-group-modal.scss';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('sw-product-warehouse-group-modal', {
    template,
    inject: [
        'acl',
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification'),
    ],
    props: {
        product: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            warehouseGroups: null,
            activeGroupId: null,
            isLoading: false,
            sortBy: 'priority',
            sortDirection: 'DESC',
        }
    },
    computed: {
        warehouseGroupRepository() {
            return this.repositoryFactory.create('warehouse_group');
        },
        warehouseGroupCriteria() {
            const { sortBy, sortDirection } = this;

            const criteria = new Criteria();

            criteria.addFilter(Criteria.equals('products.id', this.product.id));
            criteria.addSorting(Criteria.sort(sortBy, sortDirection));

            criteria.addAggregation(
                Criteria.filter(
                    'total-stock',
                    [
                        Criteria.equals('warehouses.productWarehouses.productId', this.product.id),
                    ],
                    Criteria.terms(
                        'totalStock',
                        'warehouse_group.id',
                        null,
                        null,
                        Criteria.sum('totalStock', 'warehouses.productWarehouses.stock'),
                    ),
                )
            );

            return criteria;
        },
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getWarehouseGroups();
        },
        async getWarehouseGroups() {
            try {
                this.isLoading = true;
                this.warehouseGroups = await this.warehouseGroupRepository.search(this.warehouseGroupCriteria);

                this.activeGroupId = this.warehouseGroups.length ? this.warehouseGroups[0].id : null;
            } catch (e) {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        setActiveGroup(item) {
            this.activeGroupId = item.name;
        },
        closeModal() {
            this.$emit('modal-close');
        },
        getAggregations(warehouseGroupId) {
            const { totalStock } = this.warehouseGroups.aggregations;

            let result = {
                totalStock: 0,
            };

            totalStock.buckets.forEach((bucket) => {
                if (bucket.key !== warehouseGroupId) {
                    return;
                }

                result.totalStock = bucket.totalStock.sum;
            });

            return result;
        },
        getTotalStock(warehouseGroupId) {
            const { totalStock } = this.getAggregations(warehouseGroupId);

            return this.$tc(`sw-product.modal-warehouse-group.navigationLabelStock`, 0, { totalStock });
        },
        refreshNavigation() {
            this.getWarehouseGroups();
        },
    },
});
