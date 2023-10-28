/**
 * @package inventory
 */
import template from './sw-settings-warehouse-group-create.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-settings-warehouse-group-create', {
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
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },
    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            warehouseGroup: null,
        }
    },
    computed: {
        warehouseGroupRepository() {
            return this.repositoryFactory.create('warehouse_group');
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.initializeEntity();
        },
        initializeEntity() {
            this.warehouseGroup = this.warehouseGroupRepository.create();
        },
        saveFinish() {
            this.isSaveSuccessful = false;
        },
        onCancel() {
            this.toListing();
        },
        toListing() {
            this.$router.push({ name: 'sw.settings.warehouse.index' });
        },
        toDetail() {
            this.$router.push({ name: 'sw.settings.warehouse.group.detail', params: { id: this.warehouseGroup.id } });
        },
        setWarehouseGroupWarehouses(warehouseGroupWarehouses) {
            this.warehouseGroup.warehouses = warehouseGroupWarehouses.map(({ warehouse }) => warehouse);
        },
        async onSave() {
            try {
                this.isLoading = true;
                this.isSaveSuccessful = false;

                await this.warehouseGroupRepository.save(this.warehouseGroup);

                this.isSaveSuccessful = true;
                this.toDetail();
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
            } finally {
                this.isLoading = false;
            }
        },
    },
});
