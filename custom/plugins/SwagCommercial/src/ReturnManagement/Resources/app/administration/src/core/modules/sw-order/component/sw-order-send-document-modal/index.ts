import type CriteriaType from '@administration/core/data/criteria.data';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    computed: {
        mailTemplateCriteria(): CriteriaType {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('mailTemplateType');
            criteria.addFilter(
                Criteria.equalsAny(
                    'mailTemplateType.technicalName',
                    [
                        'delivery_mail',
                        'invoice_mail',
                        'credit_note_mail',
                        'cancellation_mail',
                        'partial_cancellation_mail',
                    ],
                ),
            );

            return criteria;
        },
    },

    methods: {
        setEmailTemplateAccordingToDocumentType(): void {
            const documentMailTemplateMapping = {
                invoice: 'invoice_mail',
                credit_note: 'credit_note_mail',
                delivery_note: 'delivery_mail',
                storno: 'cancellation_mail',
                partial_cancellation: 'partial_cancellation_mail',
            };

            if (!documentMailTemplateMapping.hasOwnProperty(this.document.documentType.technicalName)) {
                return;
            }

            this.mailTemplateRepository.search(this.mailTemplateCriteria, Shopware.Context.api).then((result) => {
                const mailTemplate = result.find((mailTemplate) => mailTemplate.mailTemplateType.technicalName === documentMailTemplateMapping[this.document.documentType.technicalName]);

                if (!mailTemplate) {
                    return;
                }

                this.mailTemplateId = mailTemplate.id;
                this.onMailTemplateChange(mailTemplate.id, mailTemplate);
            });
        },
    },
});
