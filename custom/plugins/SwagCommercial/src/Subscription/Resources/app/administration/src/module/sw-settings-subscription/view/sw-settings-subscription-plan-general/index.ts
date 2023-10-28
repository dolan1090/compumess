import template from './sw-settings-subscription-plan-general.html.twig';
import './sw-settings-subscription-plan-general.scss';
import type { ComponentHelper, TEntityCollection } from '../../../../type/types';
import type { PlanState } from '../../../../state/plan.store';

const { mapState, mapPropertyErrors } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['acl'],

    props: {
        isPlanLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    computed: {
        ...mapPropertyErrors('plan', [
            'name',
            'minimumExecutionCount',
            'discountPercentage',
            'label',
        ]),

        ...mapState<PlanState>('swSubscriptionPlan', [
            'plan',
        ]),

        labelFieldDisabled(): boolean {
            return !this.acl.can('plans_and_intervals.editor') || !this.plan.activeStorefrontLabel;
        }
    },

    methods: {
        onSaveRule(ruleId: string): void {
            Shopware.State.commit('swSubscriptionPlan/setRuleId', ruleId);
        },

        onChangeIntervals(intervals: TEntityCollection<'subscription_interval'>): void {
            Shopware.State.commit('swSubscriptionPlan/setPlanIntervals', intervals);
        },
    },
});
