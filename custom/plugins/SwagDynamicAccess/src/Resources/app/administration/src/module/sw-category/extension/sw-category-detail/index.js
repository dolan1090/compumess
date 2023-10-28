const { Component } = Shopware;

Component.override('sw-category-detail', {
    computed: {
        categoryCriteria() {
            const criteria = this.$super('categoryCriteria');
            criteria.addAssociation('swagDynamicAccessRules');

            return criteria;
        },

        landingPageCriteria() {
            const criteria = this.$super('landingPageCriteria');
            criteria.addAssociation('swagDynamicAccessRules');

            return criteria;
        },
    },
});
