import template from './sw-flow-detail.html.twig';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import './sw-flow-detail.scss'

/**
 * @package business-ops
 */
export default {
    template,

    computed: {
        hasWebhookActions(): EntityCollection<'flow_sequence'>  {
            return this.sequences.some(item =>
                item.actionName === this.flowBuilderService.getActionName('CALL_WEBHOOK')
            );
        }
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },
    },
};
