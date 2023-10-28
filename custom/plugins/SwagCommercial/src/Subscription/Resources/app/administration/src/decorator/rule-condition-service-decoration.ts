import type RuleConditionService from 'src/app/service/rule-condition.service';

/**
 * @package checkout
 *
 * @private
 */
Shopware.Application.addServiceProviderDecorator(
    'ruleConditionDataProviderService',
    (ruleConditionService: RuleConditionService) => {
        ruleConditionService.upsertGroup('subscription', {
            id: 'subscription',
            name: 'global.sw-condition.group.subscription',
        });

        ruleConditionService.addCondition('subscriptionCart', {
            component: 'sw-condition-generic',
            label: 'global.sw-condition.condition.subscriptionCartRule',
            scopes: ['global'],
            group: 'subscription',
        });

        ruleConditionService.addCondition('subscriptionInterval', {
            component: 'sw-condition-generic',
            label: 'global.sw-condition.condition.subscriptionIntervalRule',
            scopes: ['global'],
            group: 'subscription',
        });

        ruleConditionService.addCondition('subscriptionPlan', {
            component: 'sw-condition-generic',
            label: 'global.sw-condition.condition.subscriptionPlanRule',
            scopes: ['global'],
            group: 'subscription',
        });

        return ruleConditionService;
    },
);
