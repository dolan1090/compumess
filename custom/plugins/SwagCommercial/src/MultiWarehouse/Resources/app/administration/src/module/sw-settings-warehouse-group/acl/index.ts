/**
 * @package inventory
 */

/* istanbul ignore next */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'settings',
        key: 'warehouse-group',
        roles: {
            viewer: {
                privileges: [
                    'warehouse_group:read',
                ],
                dependencies: [
                    'warehouse.viewer',
                    'rule.viewer',
                ],
            },
            editor: {
                privileges: [
                    'warehouse_group:update',
                ],
                dependencies: [
                    'warehouse.viewer',
                    'rule.creator',
                ],
            },
            creator: {
                privileges: [
                    'warehouse_group:create',
                ],
                dependencies: [
                    'warehouse.viewer',
                    'rule.creator',
                ],
            },
            deleter: {
                privileges: [
                    'warehouse_group:delete',
                ],
            },
        },
    });
}
