/**
 * @package inventory
 */

/* istanbul ignore next */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'settings',
        key: 'warehouse',
        roles: {
            viewer: {
                privileges: [
                    'warehouse:read',
                    'warehouse_product:read',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'warehouse:update',
                    'warehouse_product:update',
                ],
                dependencies: [
                    'warehouse.viewer',
                ],
            },
            creator: {
                privileges: [
                    'warehouse:create',
                    'warehouse_product:create',
                ],
                dependencies: [
                    'warehouse.viewer',
                ],
            },
            deleter: {
                privileges: [
                    'warehouse:delete',
                    'warehouse_product:delete',
                ],
                dependencies: [
                    'warehouse.viewer',
                ],
            },
        },
    });
}
