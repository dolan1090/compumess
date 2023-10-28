const { Application, Feature } = Shopware;

Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addAwarenessConfiguration(
        'swagDynamicAccessProducts',
        {
            notEquals: [
                'timeRange',
            ],
            snippet: 'sw-restricted-rules.restrictedAssignment.swagDynamicAccessProducts',
        },
    );

    ruleConditionService.addAwarenessConfiguration(
        'swagDynamicAccessCategories',
        {
            notEquals: [
                'timeRange',
            ],
            snippet: 'sw-restricted-rules.restrictedAssignment.swagDynamicAccessCategories',
        },
    );

    ruleConditionService.addAwarenessConfiguration(
        'swagDynamicAccessLandingPages',
        {
            notEquals: [
                'timeRange',
            ],
            snippet: 'sw-restricted-rules.restrictedAssignment.swagDynamicAccessLandingPages',
        },
    );

    return ruleConditionService;
});
