/**
 * @package inventory
 */
import template from './sw-settings-warehouse-index.html.twig';

Shopware.Component.register('sw-settings-warehouse-index', {
    template,
    inject: [
        'acl'
    ],
    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },
});
