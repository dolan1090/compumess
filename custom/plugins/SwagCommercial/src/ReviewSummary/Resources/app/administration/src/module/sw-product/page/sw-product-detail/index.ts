/**
 * @package inventory
 */

/* istanbul ignore else */
Shopware.Component.override('sw-product-detail', {
    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('reviewSummaries.translations');

            return criteria;
        },
    },
});
