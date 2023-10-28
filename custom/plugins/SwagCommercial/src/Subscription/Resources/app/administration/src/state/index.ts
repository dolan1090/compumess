import subscriptionStore from './subscription.store';
import intervalStore from './interval.store';
import planStore from './plan.store';

/**
 * @package checkout
 *
 * @public
 */
export default {
    namespaced: true,
    modules: {
        plan: planStore,
        interval: intervalStore,
        subscription: subscriptionStore,
    },
};
