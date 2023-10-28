const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-modal-grid', {
    watch: {
        salesChannelTypes() {
            if (this.isLoading) {
                return;
            }

            this.salesChannelTypes.forEach(this.addMetaData);
        },
    },
    computed: {
        salesChannelTypeRepository() {
            const repository = this.$super('salesChannelTypeRepository');
            const search = repository.search;

            repository.search = (criteria, ...rest) => {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [Criteria.equals('id', Shopware.Defaults.SalesChannelTypeSocialShopping)],
                ));

                return search.call(repository, criteria, ...rest);
            }

            return repository;
        },
    },
    methods: {
        addMetaData(salesChannelType) {
            const customFields = salesChannelType.customFields;

            if (!customFields || customFields.isSocialShoppingType !== true) {
                return;
            }

            salesChannelType.translated.name = this.$tc(salesChannelType.translated.name);
            salesChannelType.translated.manufacturer = this.$tc(salesChannelType.translated.manufacturer);
            salesChannelType.translated.description = this.$tc(salesChannelType.translated.description);
            salesChannelType.translated.descriptionLong = this.$tc(salesChannelType.translated.descriptionLong);
        },
    },
});
