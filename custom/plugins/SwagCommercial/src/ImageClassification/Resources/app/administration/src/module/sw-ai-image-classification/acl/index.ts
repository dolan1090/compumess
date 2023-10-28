/**
 * @package admin
 */

/* istanbul ignore next */
if (Shopware.License.get('IMAGE_CLASSIFICATION-2910311')) {
    Shopware.Service('privileges').addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'settings',
        key: 'ai-image',
        roles: {
            viewer: {
                privileges: [
                    'ai-image:read',
                    'ai-image_product:read',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'ai-image:update',
                    'ai-image_product:update',
                ],
                dependencies: [
                    'ai-image.viewer',
                ],
            },
        },
    });
}