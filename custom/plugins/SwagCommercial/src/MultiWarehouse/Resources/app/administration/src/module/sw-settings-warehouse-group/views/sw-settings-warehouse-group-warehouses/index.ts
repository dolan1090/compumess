/**
 * @package inventory
 */
import template from './sw-settings-warehouse-group-warehouses.html.twig';
import './sw-settings-warehouse-group-warehouses.scss';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('sw-settings-warehouse-group-warehouses', {
    template,
    inject: [
        'acl',
        'repositoryFactory',
        'syncService',
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
        localMode: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            warehouseGroupWarehouses: null,
            isLoading: false,
            showSelectionModal: false,
            showDeleteModal: false,
            limit: 10,
            total: 0,
            term: '',
            sortBy: 'warehouse.name',
        }
    },
    computed: {
        showListing() {
            const { isLoading, total, term } = this;

            return isLoading || total || term;
        },
        repository() {
            const repository = this.repositoryFactory.create('warehouse_group_warehouse');

            repository.save = this.upsertEntity.bind(this);

            return repository;
        },
        warehouseRepository() {
            return this.repositoryFactory.create('warehouse');
        },
        columns() {
            return [{
                property: 'warehouse.name',
                label: 'sw-settings-warehouse-group.warehouseAssignment.columnWarehouseName',
                primary: true,
            }, {
                property: 'priority',
                label: 'sw-settings-warehouse-group.warehouseAssignment.columnWarehousePriority',
                inlineEdit: 'number',
            }];
        },
        selectedWarehouseIds() {
            return (this.warehouseGroupWarehouses ?? []).map(({ warehouseId }) => warehouseId);
        },
    },
    methods: {
        onChangeSearchTerm(searchTerm) {
            this.term = searchTerm;
            this.page = 1;

            this.getList();
        },
        getCriteria() {
            const { page, limit, term, sortBy, sortDirection, naturalSorting } = this;

            const criteria = new Criteria(page, limit)
                .setTerm(term)
                .addSorting(Criteria.sort(sortBy, sortDirection, naturalSorting));

            if (term) {
                criteria.addFilter(Criteria.contains('warehouse.name', term));
            }

            criteria.addFilter(Criteria.equals('warehouseGroupId', this.warehouseGroupId));

            criteria.addAssociation('warehouse');
            criteria.addAssociation('warehouseGroup');

            return criteria;
        },
        onUpdateRecords(warehouseGroupWarehouses) {
            this.warehouseGroupWarehouses = warehouseGroupWarehouses;
            this.total = warehouseGroupWarehouses.total;
            this.limit = warehouseGroupWarehouses.criteria.limit;
            this.page = warehouseGroupWarehouses.criteria.page;

            this.getWarehouseDetails();
        },
        onClickAddWarehouses() {
            this.showSelectionModal = true;
        },
        onConfirmSelection(selection) {
            this.showSelectionModal = false;

            if (!selection.length) {
                return;
            }

            this.createEntities(selection);
        },
        createEntities(addedWarehouses) {
            const allSelected = this.createCollection(addedWarehouses);

            this.saveCollection(allSelected);

            this.$emit('selected-entities', allSelected, addedWarehouses);
        },
        createCollection(warehouses) {
            const { warehouseGroupId, warehouseGroupWarehouses } = this;
            const collection = warehouseGroupWarehouses ?? new EntityCollection(this.repository.route, this.repository.entityName);

            warehouses.forEach((warehouse) => {
                const entity = this.repository.create();
                entity.warehouseId = warehouse.id;
                entity.warehouseGroupId = warehouseGroupId;
                entity.priority = 1;
                entity.warehouse = warehouse;

                collection.add(entity);
            });

            return collection;
        },
        saveCollection(collection) {
            collection.criteria = this.getCriteria();
            collection.total = collection.length;
            this.warehouseGroupWarehouses = collection;
            this.total = collection.total;
        },
        onCancelDelete() {
            this.showDeleteModal = false;
        },
        async onConfirmDelete(item) {
            try {
                if (this.localMode) {
                    this.warehouseGroupWarehouses = this.warehouseGroupWarehouses.filter((_item) => _item.id !== item.id);
                } else {
                    this.isLoading = true;
                    await this.deleteWarehouseAssociation(item.warehouseId);
                }
            } finally {
                this.showDeleteModal = false;
            }
        },
        async getWarehouseGroupWarehouses() {
            this.warehouseGroupWarehouses = await this.repository.search(this.getCriteria());
            this.total = this.warehouseGroupWarehouses ? this.warehouseGroupWarehouses.total : 0;
        },
        async getWarehouseDetails() {
            const warehouseIds = this.warehouseGroupWarehouses.map(({ warehouseId }) => warehouseId);
            const criteria = new Criteria(1, this.limit)
                .setIds(warehouseIds);

            const warehouses = await this.warehouseRepository.search(criteria);

            this.warehouseGroupWarehouses.forEach((wgw) => {
                wgw.warehouse = warehouses.get(wgw.warehouseId);
            });
        },
        async getList() {
            try {
                if (this.localMode) {
                    return;
                }

                this.isLoading = true;

                await this.getWarehouseGroupWarehouses();
                await this.getWarehouseDetails();
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        async deleteWarehouseAssociation(warehouseId) {
            try {
                const payload = [{
                    action: 'delete',
                    entity: 'warehouse_group_warehouse',
                    payload: [{
                        'warehouseGroupId': this.warehouseGroupId,
                        'warehouseId': warehouseId,
                    }],
                }];

                await this.syncService.sync(payload);
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.getList();
            }
        },
        upsertEntity(entity) {
            return this.syncService.sync([{
                action: 'upsert',
                entity: 'warehouse_group_warehouse',
                payload: [ entity ],
            }]);
        }
    },
});
