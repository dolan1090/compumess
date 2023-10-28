Shopware.Component.override('sw-order-detail', {
    computed: {
        orderCriteria() {
            const defaultCriteria = this.$super('orderCriteria');

            defaultCriteria.addAssociation('swagSocialShoppingOrder.salesChannel');

            return defaultCriteria;
        },
    }
});
