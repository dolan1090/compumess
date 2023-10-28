/**
 * @package inventory
 */
import template from './sw-settings-warehouse-group-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-settings-warehouse-group-detail', {
    template,
    inject: [
        'repositoryFactory',
        'acl',
    ],
    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder'),
    ],
    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.acl.can('warehouse-group.editor');
            },
            method: 'onSave',
        },
        ESCAPE: 'toListing',
    },
    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            warehouseGroup: null,
            showDeleteModal: false,
            productWarehouses: [],
        }
    },
    metaInfo() {
        return {
            title: this.$createTitle(this.placeholder(this.warehouseGroup, 'name')),
        };
    },
    computed: {
        warehouseGroupId() {
            return this.$router.currentRoute.params.id;
        },
        warehouseGroupCriteria() {
            const criteria = new Criteria();

            criteria.addAssociation('products');
            criteria.getAssociation('products').addAssociation('warehouses');

            return criteria;
        },
        warehouseGroupRepository() {
            return this.repositoryFactory.create('warehouse_group');
        },
        productWarehouseRepository() {
            return this.repositoryFactory.create('product_warehouse');
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getWarehouseGroup();
        },
        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },
        setWarehouseGroupWarehouses(warehouseGroupWarehouses, addedWarehouses = []) {
            this.warehouseGroup.warehouses = warehouseGroupWarehouses.map(({ warehouse }) => warehouse);
        },
        async getWarehouseGroup() {
            try {
                this.isLoading = true;
                this.warehouseGroup = await this.warehouseGroupRepository.get(
                    this.warehouseGroupId, Shopware.Context.api, this.warehouseGroupCriteria
                );
            } catch (e) {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        async onConfirmDelete() {
            try {
                this.isLoading = true;
                this.showDeleteModal = false;

                await this.warehouseGroupRepository.delete(this.warehouseGroupId);

                this.toListing();
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-warehouse-group.general.notificationGeneric'),
                });
            } finally {
                this.isLoading = false;
            }
        },
        async onSave() {
            try {
                this.isLoading = true;
                this.isSaveSuccessful = false;

                await this.warehouseGroupRepository.save(this.warehouseGroup);
                await this.productWarehouseRepository.saveAll(this.productWarehouses);
                await this.getWarehouseGroup();

                this.isSaveSuccessful = true;
            } catch (error) {
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
        toListing() {
            this.$router.push({ name: 'sw.settings.warehouse.index' });
        },
        /**
         * @deprecated tag:v6.6.0 - Will be removed
         */
        findNewProductWarehouses(addedWarehouses) {
            let result = [];

            addedWarehouses.forEach((warehouse) => {
                this.warehouseGroup.products.forEach((product) => {
                    const hasWarehouse = product.extensions.warehouses.some((productWarehouse) => {
                        return productWarehouse.warehouseId === warehouse.id;
                    });

                    if (hasWarehouse) {
                        return;
                    }

                    result.push(this.createProductWarehouse(warehouse.id, product.id, product.versionId));
                });
            });

            this.productWarehouses = result;
            return result;
        },
        /**
         * @deprecated tag:v6.6.0 - Will be removed
         */
        createProductWarehouse(warehouseId, productId, productVersionId) {
            const productWarehouse = this.productWarehouseRepository.create();

            Object.assign(productWarehouse, {
                warehouseId,
                productId,
                productVersionId,
                stock: 0,
                availableStock: 0,
            });

            return productWarehouse;
        },
    },
});
