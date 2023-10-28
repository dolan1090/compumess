import type { TEntity } from '../type/types';

export interface SubscriptionState {
    subscription: TEntity<'subscription'>,
    isLoading: boolean,
}

/**
 * @package checkout
 *
 * @public
 */
export default {
    namespaced: true,

    state: (): SubscriptionState => ({
        subscription: {} as TEntity<'subscription'>,
        isLoading: false,
    }),

    mutations: {
        setSubscription: (state: SubscriptionState, subscription: TEntity<'subscription'>): void => {
            state.subscription = subscription;
        },

        setLoading: (state: SubscriptionState, isLoading: boolean): void => {
            state.isLoading = isLoading;
        },
    },

    actions: {},

    getters: {},
};
