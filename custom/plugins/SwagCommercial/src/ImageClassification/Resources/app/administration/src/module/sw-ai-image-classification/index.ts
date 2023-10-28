/**
 * @package admin
 */

import './acl';

Shopware.Component.register('sw-ai-image-classification', () => import('./page/sw-ai-image-classification/index'));
// @ts-expect-error - override component can not be a valid vue component
Shopware.Component.override('sw-media-quickinfo', () => import('./extensions/sw-media-quickinfo/index'));

Shopware.Module.register('sw-ai-image-classification', {
    type: 'plugin',
    name: 'ai-image-classification',
    title: 'sw-ai-image-classification.general.mainMenuItemGeneral',
    icon: 'regular-image',
    color: '#9AA8B5',

    routes: {
        index: {
            component: 'sw-ai-image-classification',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'ai-image.viewer',
            }
        }
    },

    settingsItem: {
        group: 'plugins',
        to: 'sw.ai.image.classification.index',
        icon: 'regular-image',
        iconComponent: undefined, // needed for TS, we need to fix this in platform
        privilege: 'ai-image.viewer',
    }
})

