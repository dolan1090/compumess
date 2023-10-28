/**
 * @package checkout
 */

Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'subscription',
    key: 'plans_and_intervals',
    roles: {
        viewer: {
            privileges: [
                'subscription_plan:read',
                'subscription_interval:read',
                'product:read',
                'rule_condition:read',
                'subscription:read',
                'property_group_option:read',
                'property_group:read',
            ],
            dependencies: [],
        },
        editor: {
            privileges: [
                'subscription_plan:update',
                'subscription_interval:update',
                'subscription_plan_product_mapping:create',
                'subscription_plan_product_mapping:delete',
            ],
            dependencies: [
                'plans_and_intervals.viewer',
            ],
        },
        creator: {
            privileges: [
                'subscription_plan:create',
                'subscription_interval:create',
                'subscription_plan_interval_mapping:create',
            ],
            dependencies: [
                'plans_and_intervals.editor',
                'plans_and_intervals.viewer',
            ],
        },
        deleter: {
            privileges: [
                'subscription_plan:delete',
                'subscription_interval:delete',
            ],
            dependencies: [
                'plans_and_intervals.viewer',
            ],
        },
    },
});
