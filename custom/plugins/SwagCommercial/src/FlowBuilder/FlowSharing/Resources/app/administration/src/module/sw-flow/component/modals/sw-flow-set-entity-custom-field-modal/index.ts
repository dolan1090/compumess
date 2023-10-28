import template from './sw-flow-set-entity-custom-field-modal.html.twig';

/**
 * @private
 * @package business-ops
 */
export default {
    template,

    methods: {
        createdComponent(): void {
            this.getEntityOptions();
            if (!this.sequence.config) {
                return;
            }

            this.entity = this.sequence.config.entity;
            this.customFieldSetId = this.sequence.config.customFieldSetId;
            this.customFieldSetLabel = this.sequence.config.customFieldSetLabel;
            this.customFieldId = this.sequence.config.customFieldId;
            this.customFieldLabel = this.sequence.config.customFieldLabel;
            this.customFieldValue = this.sequence.config.customFieldValue;
            this.fieldOptionSelected = this.sequence.config.option;
            this.fieldOptions = this.defaultFieldOptions;
        },
    }
};
