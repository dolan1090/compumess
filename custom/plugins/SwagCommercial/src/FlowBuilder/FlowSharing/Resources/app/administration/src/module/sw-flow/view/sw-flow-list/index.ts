import template from './sw-flow-list.html.twig';
import type {FlowEntity} from '../../flow.types';

/**
 * @package business-ops
 */
export default {
    template,

    data(): {
        currentFlow: FlowEntity,
        isDownloading: boolean,
    } {
        return {
            currentFlow: {} as FlowEntity,
            isDownloading: false,
        };
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        onOpenDownloadModal(item: FlowEntity): void {
            this.isDownloading = true;
            this.currentFlow = item;
        },

        onCloseDownloadModal(): void {
            this.isDownloading = false;
            this.currentFlow = {};
        },

        onDownloadFlowSuccess(isSuccess: boolean): void {
            this.isDownloading = false;
            this.currentFlow = {};

            if (isSuccess) {
                this.createNotificationSuccess({
                    message: this.$tc('sw-flow-sharing.notification.messageDownloadSuccess'),
                });

                return;
            }

            this.createNotificationError({
                message: this.$tc('sw-flow-sharing.notification.messageDownloadError'),
            });
        },
    },
};
