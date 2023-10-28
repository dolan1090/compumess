import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import { DocumentTypes } from '../../../../../type/types.d';

interface DocumentTypeOption {
    value: string,
    name: string,
    disabled: boolean,
    helpText?: string
}

const { Component } = Shopware;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    watch: {
        documentTypes: {
            handler(value) {
                if (!value?.length) {
                    return;
                }

                const partialCancellationType = this.documentTypeCollection.find(documentType => documentType.technicalName === DocumentTypes.PARTIAL_CANCELLATION);
                const partialCancellationTypeIndex = this.documentTypes.findIndex(item => item.value === partialCancellationType.id);

                this.documentTypes[partialCancellationTypeIndex] = this.addHelpTextToOption(this.documentTypes[partialCancellationTypeIndex],
                    partialCancellationType);
            },
            immediate: true
        }
    },

    methods: {
        documentTypeAvailable(documentType: Entity<'document_type'>): boolean {
            return (
                (
                    documentType.technicalName !== DocumentTypes.STORNO &&
                    documentType.technicalName !== DocumentTypes.CREDIT_NOTE &&
                    documentType.technicalName !== DocumentTypes.PARTIAL_CANCELLATION
                ) ||
                (
                    (
                        documentType.technicalName === DocumentTypes.STORNO ||
                        documentType.technicalName === DocumentTypes.PARTIAL_CANCELLATION ||
                        (
                            documentType.technicalName === DocumentTypes.CREDIT_NOTE &&
                            this.creditItems.length !== 0
                        )
                    ) && this.invoiceExists
                )
            );
        },

        addHelpTextToOption(option: DocumentTypeOption, documentType: Entity<'document_type'>): DocumentTypeOption {
            option.helpText = documentType.technicalName === DocumentTypes.PARTIAL_CANCELLATION
                ? this.$tc('swag-return-management.documentConfig.helpTextPartialCancellation')
                : this.$tc(`sw-order.components.selectDocumentTypeModal.helpText.${documentType.technicalName}`);

            return option;
        },
    },
});
