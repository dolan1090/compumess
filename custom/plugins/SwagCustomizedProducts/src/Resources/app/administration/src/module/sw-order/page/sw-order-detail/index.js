const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-order-detail', {
    computed: {
        orderCriteria() {
            const criteria = this.$super('orderCriteria');

            criteria
                .getAssociation('lineItems.children.children')
                .addSorting(Criteria.naturalSorting('label'));

            return criteria;
        },
    },
});
