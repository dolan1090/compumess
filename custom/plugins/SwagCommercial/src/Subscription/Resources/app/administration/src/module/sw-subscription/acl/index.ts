/**
 * @package checkout
 */

Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'subscription',
    key: 'subscription',
    roles: {
        viewer: {
            privileges: [
                'subscription:read',
                'subscription_address:read',
                'subscription_customer:read',
                'subscription_tag_mapping:read',
                'subscription_plan:read',
                'subscription_interval:read',
                'system_config:read',
            ],
            dependencies: [
                'order.viewer',
                'product.viewer',
            ],
        },
        editor: {
            privileges: [
                'subscription:update',
                'subscription_address:update',
                'subscription_customer:update',
                'subscription_tag_mapping:update',
            ],
            dependencies: [
                'subscription.viewer',
            ],
        },
        deleter: {
            privileges: [
                'subscription:delete',
            ],
            dependencies: [
                'subscription.viewer',
            ],
        },
    },
});
