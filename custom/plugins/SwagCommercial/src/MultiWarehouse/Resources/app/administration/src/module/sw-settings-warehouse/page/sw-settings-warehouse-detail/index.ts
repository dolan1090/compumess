/**
 * @package inventory
 */
import template from './sw-settings-warehouse-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('sw-settings-warehouse-detail', {
    template,
    inject: [
        'repositoryFactory',
        'acl'
    ],
    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],
    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.acl.can('warehouse.editor');
            },
            method: 'onSave',
        },
        ESCAPE: 'onCancel',
    },
    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            warehouse: null,
            showDeleteModal: false,
            productWarehouses: [],
        }
    },
    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },
    computed: {
        warehouseId() {
            return this.$router.currentRoute.params.id;
        },
        warehouseCriteria() {
            return new Criteria()
                .addAssociation('groups')
                .addAssociation('productWarehouses');
        },
        warehouseRepository() {
            return this.repositoryFactory.create('warehouse', '', { useSync: true });
        },
        productWarehouseRepository() {
            return this.repositoryFactory.create('product_warehouse');
        },
        identifier() {
            return this.placeholder(this.warehouse, 'name');
        },
        ...mapPropertyErrors(
            'warehouse',
            ['name'],
        ),
        warehouseGroupSelectCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('products')
            criteria.getAssociation('products')
                .addAssociation('warehouses');
            criteria.getAssociation('products')
                .getAssociation('warehouses')
                .addFilter(Criteria.equals('warehouseId', this.warehouse.id));

            criteria.addIncludes({
                product: ['id', 'versionId', 'productWarehouses'],
                productWarehouses: ['id', 'warehouseId', 'productId', 'productVersionId'],
            });

            return criteria;
        },
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getWarehouse();
        },
        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },
        async getWarehouse() {
            try {
                this.isLoading = true;
                this.warehouse = await this.warehouseRepository.get(this.warehouseId, Shopware.Context.api, this.warehouseCriteria);
            } catch(e) {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        async onConfirmDelete() {
            try {
                this.isLoading = true;
                this.showDeleteModal = false;

                await this.warehouseRepository.delete(this.warehouseId);
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
                this.toListing();
            }
        },
        async onSave() {
            try {
                this.isLoading = true;
                this.isSaveSuccessful = false;

                await this.warehouseRepository.save(this.warehouse);
                await this.getWarehouse();

                this.isSaveSuccessful = true;
            }
            catch (error) {
                const errorDetail = error.response?.data.errors[0]?.detail;
                const titleSaveError = this.$tc('sw-settings-warehouse-group.general.notificationGeneric');
                const messageSaveError = errorDetail ?? this.$tc(
                    'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                );

                this.createNotificationError({
                    title: titleSaveError,
                    message: messageSaveError,
                });
            }
            finally {
                this.isLoading = false;
            }
        },
        saveFinish() {
            this.isSaveSuccessful = false;
        },
        updateGroupCollection(updatedGroups) {
            this.warehouse.groups = updatedGroups;
        },
        toListing() {
            this.$router.push({ name: 'sw.settings.warehouse.index' });
        },
        onCancel() {
            this.toListing();
        },
        /**
         * @deprecated tag:v6.6.0 - Will be removed
         */
        createMissingProductWarehouses() {
            const productWarehouses = new EntityCollection(this.productWarehouseRepository.route, this.productWarehouseRepository.entityName);
            this.warehouse.groups.forEach((warehouseGroup) => {
                warehouseGroup.products.forEach((product) => {
                    if (product.extensions && product.extensions.warehouses.length) {
                        productWarehouses.add(product.extensions.warehouses[0]);
                        return;
                    }

                    productWarehouses.add(this.createProductWarehouse(product.id, product.versionId));
                });
            });

            this.warehouse.productWarehouses = productWarehouses;
        },
        /**
         * @deprecated tag:v6.6.0 - Will be removed
         */
        createProductWarehouse(productId, productVersionId) {
            const productWarehouse = this.productWarehouseRepository.create();

            Object.assign(productWarehouse, {
                warehouseId: this.warehouse.id,
                productId: productId,
                productVersionId: productVersionId,
                stock: 0,
            });

            return productWarehouse;
        },
    },
});
