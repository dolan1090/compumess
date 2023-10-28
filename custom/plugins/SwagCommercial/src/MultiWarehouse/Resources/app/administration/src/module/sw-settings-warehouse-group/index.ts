/**
 * @package inventory
 */
import './acl';
import './snippet';
import './page/sw-settings-warehouse-group-detail';
import './page/sw-settings-warehouse-group-create';
import './views/sw-settings-warehouse-group-list';
import './views/sw-settings-warehouse-group-warehouses';
import './component/sw-warehouse-selection';
import './component/sw-warehouse-group-form';

/* istanbul ignore next */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Module.register('sw-settings-warehouse-group', {
        type: 'plugin',
        name: 'settings-warehouse-group',
        title: 'sw-settings-warehouse-group.general.title',
        description: 'sw-settings-warehouse-group.general.description',
        entity: 'warehouse_group',
        icon: 'regular-cog',
        color: '#9AA8B5',

        routes: {
            detail: {
                component: 'sw-settings-warehouse-group-detail',
                path: 'detail/:id',
                meta: {
                    parentPath: 'sw.settings.warehouse.index',
                    privilege: 'warehouse-group.viewer'
                }
            },
            create: {
                component: 'sw-settings-warehouse-group-create',
                path: 'create',
                meta: {
                    parentPath: 'sw.settings.warehouse.index',
                    privilege: 'warehouse-group.creator'
                }
            }
        },
    });
}
