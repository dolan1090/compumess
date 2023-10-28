import type { TEntityCollection, TEntity } from '../type/types';

export interface PlanState {
    plan: TEntity<'subscription_plan'>,
    planProducts: TEntityCollection<'product'>,
}

/**
 * @package checkout
 *
 * @public
 */
export default {
    namespaced: true,

    state: (): PlanState => ({
        plan: {} as TEntity<'subscription_plan'>,
        planProducts: new Shopware.Data.EntityCollection('', 'product', Shopware.Context.api),
    }),

    mutations: {
        setPlan: (state: PlanState, plan: TEntity<'subscription_plan'>): void => {
            state.plan = plan;
        },

        setRuleId: (state: PlanState, ruleId: string): void => {
            state.plan.availabilityRuleId = ruleId;
        },

        setPlanIntervals: (state: PlanState, planIntervals: TEntity<'subscription_interval'>): void => {
            state.plan.subscriptionIntervals = planIntervals;
        },

    },

    actions: {},

    getters: {},
};
