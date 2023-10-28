import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import template from './sw-order-detail-general.html.twig';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    methods: {
        saveAndReloadOrder(): void {
            this.$emit('save-and-reload');
        },

        saveEdits() {
            this.$emit('save-edits');
        },
    }
});
