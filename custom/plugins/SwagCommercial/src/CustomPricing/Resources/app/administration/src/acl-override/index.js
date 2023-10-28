/**
 * @package inventory
 */

if (Shopware.License.get('CUSTOM_PRICES-1673073')) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: null,
        key: 'custom_price',
        roles: {
            viewer: {
                privileges: [
                    'custom_price:read',
                    'product:read',
                    'customer:read',
                    'customer_group:read',
                    'currency:read',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'custom_price:update',
                ],
                dependencies: [
                    'custom_price.viewer',
                ],
            },
            creator: {
                privileges: [
                    'custom_price:create',
                ],
                dependencies: [
                    'custom_price.viewer',
                    'custom_price.editor',
                    'custom_price.deleter',
                ],
            },
            deleter: {
                privileges: [
                    'custom_price:delete',
                ],
                dependencies: [
                    'custom_price.viewer',
                ],
            },
        },
    });
}
