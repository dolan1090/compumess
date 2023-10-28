import "./sw-product-add-properties-modal.scss";
import template from './sw-product-add-properties-modal.html.twig';
import './sw-product-add-properties-modal.scss';

/**
 * @package business-ops
 */
export default {
    template,

    data() {
        return {
            showAssistantModal: false,
        }
    },

    methods: {
        onOpenAssistantModal() {
            if (Shopware.License.get('PROPERTY_EXTRACTOR-1056151')) {
                const initContainer = Shopware.Application.getContainer('init');
                initContainer.httpClient.get(
                    'api/_info/config',
                    {
                        headers: {
                            Accept: 'application/vnd.api+json',
                            Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                            'Content-Type': 'application/json',
                            'sw-license-toggle': 'PROPERTY_EXTRACTOR-1056151',
                        },
                    },
                );
                return;
            }

            this.showAssistantModal = true;
        },

        onCloseAssistantModal() {
            this.showAssistantModal = false;
        },

        async onAssistantModalSave(event) {
            this.showAssistantModal = false;

            this.newProperties.push(...event.filter((newItem) => {
                return this.newProperties.findIndex(item => item.id === newItem.id) === -1
            }));

            await Promise.all([
                this.$refs.propertySearch.loadGroups(),
                this.$refs.propertySearch.loadOptions()
            ]);
        }
    },
};
