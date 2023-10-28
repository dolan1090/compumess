/**
 * @package inventory
 */
import './page/sw-settings-translator-index';
import './snippet';

if (Shopware.License.get('REVIEW_TRANSLATOR-1649854')) {
    Shopware.Module.register('sw-settings-translator', {
        type: 'plugin',
        name: 'sw-settings-translator',
        title: 'sw-settings-translator.general.title',
        icon: 'regular-cog',
        color: '#9AA8B5',

        routes: {
            index: {
                component: 'sw-settings-translator-index',
                path: 'index',
                meta: {
                    parentPath: 'sw.settings.index',
                },
            },
        },

        settingsItem: {
            group: 'shop',
            to: 'sw.settings.translator.index',
            icon: 'regular-star',
            name: 'swag-example.general.mainMenuItemGeneral'
        },
    });
}
