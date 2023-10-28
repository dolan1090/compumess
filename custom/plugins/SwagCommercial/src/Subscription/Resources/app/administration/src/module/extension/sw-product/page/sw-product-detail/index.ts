import type { TCriteria } from '../../../../../type/types';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    computed: {
        productCriteria(): TCriteria {
            const criteria = this.$super('productCriteria')

            criteria.addAssociation('subscriptionPlans');
            criteria.addIncludes({
                subscriptionPlans: ['id', 'name'],
            });

            return criteria;
        },
    },
});
