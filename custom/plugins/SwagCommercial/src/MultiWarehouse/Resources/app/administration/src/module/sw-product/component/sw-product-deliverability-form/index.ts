/**
 * @package inventory
 */
import template from './sw-product-deliverability-form.html.twig';
import './sw-product-deliverability-form.scss';

/* istanbul ignore else */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    const { Component } = Shopware;
    const { Criteria, EntityCollection } = Shopware.Data;

    Component.override('sw-product-deliverability-form', {
        template,
        inject: [
            'acl',
            'repositoryFactory',
        ],
        data() {
            return {
                showWarehouseGroupModal: false,
                warehouseGroupSortBy: 'priority',
                warehouseGroupSortDirection: 'DESC',
                originProductWarehouses: null,
            };
        },
        computed: {
            productWarehouseRepository() {
                return this.repositoryFactory.create('product_warehouse');
            },
            isWarehouseGroupAssigned() {
                return (
                    this.product.extensions.warehouseGroups &&
                    this.product.extensions.warehouseGroups.length
                );
            },
            isProductWarehouseModalDisabled() {
                return (
                    this.product.isNew() ||
                    (
                        !this.product.extensions.warehouseGroups ||
                        !this.product.extensions.warehouseGroups.length ||
                        this.product.extensions.warehouseGroups.some((warehouseGroup) => (warehouseGroup.isNew()))
                    )
                );
            },
            warehouseGroupSelectCriteria() {
                const criteria = new Criteria();

                criteria.addAssociation('warehouses');

                return criteria;
            },
        },
        methods: {
            createdComponent() {
                this.$super('createdComponent');
                this.originProductWarehouses = this.product.extensions.warehouses;
            },
            setWarehouseGroupCollection(collection) {
                this.product.extensions.warehouseGroups = collection;
            },
            displayWarehouseGroupModal() {
                if (this.isProductWarehouseModalDisabled) {
                    return;
                }

                this.showWarehouseGroupModal = true;
            },
            closeWarehouseGroupModal() {
                this.showWarehouseGroupModal = false;
            },
            /**
             * @deprecated tag:v6.6.0 - Will be removed
             */
            createMissingProductWarehouses() {
                const { warehouseGroups } = this.product.extensions;
                const collection = new EntityCollection(this.productWarehouseRepository.route, this.productWarehouseRepository.entityName);
                const originProductWarehouses = this.originProductWarehouses;
                const warehouseIds = new Set();

                warehouseGroups.forEach(({ warehouses }) => {
                    warehouses.forEach(({ id }) => {
                        warehouseIds.add(id);
                    });
                });

                /**
                 * Currently we're not deleting ProductWarehouses in other places (like WarehouseGroup detail page).
                 * To prevent inconsistency, this is a workaround to not delete the ProductWarehouse association after unassigning a WarehouseGroup.
                 */
                originProductWarehouses.forEach(({ warehouseId }) => {
                    warehouseIds.add(warehouseId);
                });

                warehouseIds.forEach((warehouseId) => {
                    const originProductWarehouse = originProductWarehouses.find(
                        (productWarehouse) => productWarehouse.warehouseId === warehouseId
                    );

                    if (originProductWarehouse) {
                        collection.add(originProductWarehouse);
                    } else {
                        collection.add(this.createProductWarehouse(warehouseId));
                    }
                });

                this.product.extensions.warehouses = collection;
            },
            createProductWarehouse(warehouseId) {
                const productWarehouse = this.productWarehouseRepository.create();

                Object.assign(productWarehouse, {
                    warehouseId,
                    productId: this.product.id,
                    productVersionId: this.product.versionId,
                    stock: 0,
                });

                return productWarehouse;
            },
            markEntityAsNew(warehouseGroup) {
                if (this.product.getOrigin().extensions.warehouseGroups.find(({ id }) => id === warehouseGroup.id)) {
                    return;
                }

                warehouseGroup.markAsNew();
            },
        },
    });
}
