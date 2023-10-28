import type { PropType } from 'vue';
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type EntityCollection from '@shopware-ag/admin-extension-sdk/es/data/_internals/EntityCollection';
import template from './sw-order-document-settings-partial-cancellation-modal.html.twig';

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    template,

    props: {
        order: {
            type: Object as PropType<Entity<'order'>>,
            required: true,
        },
        currentDocumentType: {
            type: Object as PropType<Entity<'document_type'>>,
            required: true,
        },
    },

    data(): {
        documentConfig: {
            custom: {
                stornoNumber: string,
                invoiceNumber: string,
                includeCancelled: boolean,
            },
            documentNumber: string,
            documentComment: string,
            documentDate: string,
        },
    } {
        return {
            documentConfig: {
                custom: {
                    stornoNumber: '',
                    invoiceNumber: '',
                    includeCancelled: false,
                },
                documentNumber: '',
                documentComment: '',
                documentDate: '',
            },
        };
    },

    computed: {
        documentPreconditionsFulfilled(): boolean {
            return !!this.documentConfig.custom.invoiceNumber;
        },

        invoices(): EntityCollection<'document'> {
            return this.order.documents.filter((document: Entity<'document'>) => {
                return document.documentType.technicalName === 'invoice';
            });
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent(): Promise<void> {
            const response = await this.numberRangeService.reserve(
                `document_${this.currentDocumentType.technicalName}`,
                this.order.salesChannelId,
                true,
            );

            this.documentConfig.documentNumber = response.number;
            this.documentNumberPreview = this.documentConfig.documentNumber;
            this.documentConfig.documentDate = (new Date()).toISOString();
        },

        async onCreateDocument(additionalAction = false): Promise<void> {
            this.$emit('loading-document');

            const selectedInvoice = this.invoices.filter((item: Entity<'document'>) => {
                return item.config.custom.invoiceNumber === this.documentConfig.custom.invoiceNumber;
            })[0];

            if (this.documentNumberPreview === this.documentConfig.documentNumber) {
                try {
                    const response = await this.numberRangeService.reserve(
                        `document_${this.currentDocumentType.technicalName}`,
                        this.order.salesChannelId,
                        false,
                    );

                    this.documentConfig.custom.stornoNumber = response.number;
                    if (response.number !== this.documentConfig.documentNumber) {
                        this.createNotificationInfo({
                            message: this.$tc('sw-order.documentCard.info.DOCUMENT__NUMBER_WAS_CHANGED'),
                        });
                    }
                    this.documentConfig.documentNumber = response.number;
                    this.callDocumentCreate(additionalAction, selectedInvoice.id);
                } catch(error) {
                    this.createNotificationError({
                        message: error?.response?.data?.errors[0]?.detail,
                    });
                }
            } else {
                this.documentConfig.custom.stornoNumber = this.documentConfig.documentNumber;
                this.callDocumentCreate(additionalAction, selectedInvoice.id);
            }
        },

        onPreview(): void {
            this.$emit('loading-preview');
            this.documentConfig.custom.stornoNumber = this.documentConfig.documentNumber;
            this.$super('onPreview');
        },

        onSelectInvoice(selected: string): void {
            this.documentConfig.custom.invoiceNumber = selected;
        },
    },
});
