import type {PropType} from 'vue';
import template from './sw-flow-call-webhook-log-detail-modal.html';
import './sw-flow-call-webhook-log-detail-modal.scss';
import {WebhookAction} from '../../../../../type/types';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

/**
 * @package business-ops
 */
export default {
    template,

    props: {
        logEntry: {
            type: Object as PropType<Entity<'webhook_event_log'>>,
            required: true,
        },
    },

    computed: {
        method(): string | undefined {
            return this.logEntry?.requestContent?.method;
        }
    },

    methods: {
        onClose(): void {
            this.$emit('close');
        },

        displayString(content: WebhookAction): string | undefined {
            return JSON.stringify(content, null, 8);
        },
    },
};
