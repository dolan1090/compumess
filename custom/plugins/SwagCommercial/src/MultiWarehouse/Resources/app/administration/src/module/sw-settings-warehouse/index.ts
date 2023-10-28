/**
 * @package inventory
 */
import './acl';
import './snippet';
import './page/sw-settings-warehouse-index';
import './page/sw-settings-warehouse-detail';
import './page/sw-settings-warehouse-create';
import './views/sw-settings-warehouse-list';
import './component/sw-warehouse-label-list';
import './component/sw-warehouse-messages';

/* istanbul ignore next */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Module.register('sw-settings-warehouse', {
        type: 'plugin',
        name: 'settings-warehouse',
        title: 'sw-settings-warehouse.general.title',
        entity: 'warehouse',
        icon: 'regular-cog',
        color: '#9AA8B5',

        routes: {
            index: {
                component: 'sw-settings-warehouse-index',
                path: 'index',
                meta: {
                    parentPath: 'sw.settings.index',
                },
            },
            detail: {
                component: 'sw-settings-warehouse-detail',
                path: 'detail/:id',
                meta: {
                    parentPath: 'sw.settings.warehouse.index',
                    privilege: 'warehouse.viewer',
                },
            },
            create: {
                component: 'sw-settings-warehouse-create',
                path: 'create',
                meta: {
                    parentPath: 'sw.settings.warehouse.index',
                    privilege: 'warehouse.creator',
                },
            },
        },

        settingsItem: {
            group: 'shop',
            to: 'sw.settings.warehouse.index',
            icon: 'regular-warehouse',
            privilege: 'warehouse.viewer',
        },
    });
}
