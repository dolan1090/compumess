const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('acris-one-to-many-grid', 'sw-one-to-many-grid', {
    methods: {
        load() {
            this.repository = this.repositoryFactory.create(
                // product_price
                this.collection.entity,

                // product/{id}/price-rules/
                this.collection.source
            );

            return this.repository.search(this.result.criteria, this.result.context)
                .then((response) => {
                    this.applyResult(response)
                });
        },

        paginate(params) {
            this.result.criteria.setPage(params.page);
            this.result.criteria.setLimit(params.limit);

            return this.load();
        }
    }
});
