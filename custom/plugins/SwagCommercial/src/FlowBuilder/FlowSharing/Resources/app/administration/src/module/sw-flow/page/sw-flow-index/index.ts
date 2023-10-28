import template from './sw-flow-index.html.twig';

/**
 * @package business-ops
 */
export default {
    template,

    data(): {
        showUploadModal: boolean
    } {
        return {
            showUploadModal: false
        };
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        openUploadModal(): void {
            this.showUploadModal = true;
        },

        onCloseUploadModal(): void {
            this.showUploadModal = false;
        },

        onUploadFlowTemplateFile(): void {
            this.$router.push({
                name: 'sw.flow.create.general',
                params: { isUploading: true }
            });
        },
    },
};
