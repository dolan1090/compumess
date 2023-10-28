import { TOGGLE_KEY } from '../../../config';

if (Shopware.License.get(TOGGLE_KEY)) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'orders',
        key: 'order_return',
        roles: {
            viewer: {
                privileges: [
                    'order_return_line_item:read',
                    'order_return:read'
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'state_machine_history:create',
                    'order_return_line_item:delete',
                    'order_return_line_item:update',
                    'order_return:update'
                ],
                dependencies: [
                    'order_return.viewer',
                ],
            },
            creator: {
                privileges: [
                    'order_return:create'
                ],
                dependencies: [
                    'order_return.viewer',
                    'order_return.editor',
                ],
            },
            deleter: {
                privileges: [
                    'order_return:delete'
                ],
                dependencies: [
                    'order_return.viewer',
                ],
            },
        },
    });
}
