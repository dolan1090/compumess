import type CriteriaType from '@administration/core/data/criteria.data';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    computed: {
        defaultCriteria(): CriteriaType {
            const criteria = this.$super('defaultCriteria');
            criteria.addAssociation('specificFeatures')

            return criteria;
        },
    },
})
