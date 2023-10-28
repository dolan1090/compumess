/**
 * @package inventory
 */
import template from './sw-settings-warehouse-create.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('sw-settings-warehouse-create', {
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
        }
    },
    computed: {
        warehouseRepository() {
            return this.repositoryFactory.create('warehouse');
        },
        warehouseGroupSelectCriteria() {
            return new Criteria()
                .addAssociation('products');
        },
        productWarehouseRepository() {
            return this.repositoryFactory.create('product_warehouse');
        },
        ...mapPropertyErrors(
            'warehouse',
            ['name'],
        ),
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.initializeEntity();
        },
        initializeEntity() {
            this.warehouse = this.warehouseRepository.create();
        },
        async onSave() {
            try {
                this.isLoading = true;
                this.isSaveSuccessful = false;

                await this.warehouseRepository.save(this.warehouse);

                this.isSaveSuccessful = true;
                this.toDetail();
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
            this.warehouse.productWarehouses = updatedGroups.map((warehouseGroup) => {
                return warehouseGroup.products.map((product) => {
                    const productWarehouse = this.productWarehouseRepository.create();

                    Object.assign(productWarehouse, {
                        warehouseId: this.warehouse.id,
                        productId: product.id,
                        productVersionId: product.versionId,
                        stock: 0,
                    });

                    return productWarehouse;
                });
            }).flat();
        },
        onCancel() {
            this.toListing();
        },
        toListing() {
            this.$router.push({ name: 'sw.settings.warehouse.index' });
        },
        toDetail() {
            this.$router.push({ name: 'sw.settings.warehouse.detail', params: { id: this.warehouse.id } });
        },
    },
});
