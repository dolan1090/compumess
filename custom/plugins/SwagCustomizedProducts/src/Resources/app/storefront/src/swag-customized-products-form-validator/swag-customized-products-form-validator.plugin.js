import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

import { SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD }
    from '../swag-customized-products-file-upload/swag-customized-products-file-upload.plugin';

export default class SwagCustomizedProductsFormValidator extends Plugin {
    static options = {
        inputFieldsSelector: '.swag-customized-products-form-control:not(#swag-customized-products-one-time-share)',
        selectors: {
            buyButton: '#productDetailPageBuyProductForm .btn-buy',
            fileUploadContainer: '.customized-products-upload',
            confirmInput: '#swag-customized-products-confirm-input',
        },
    };

    init() {
        this.buyForm = this.el.parentNode;
        this.exclusionsValid = true;
        this._registerEventListeners();
    }

    /**
     * @returns {void}
     */
    _registerEventListeners() {
        this.$emitter.subscribe(
            'buyButtonDisable',
            this.updateExclusionValidity.bind(this),
        );

        this.$emitter.subscribe(
            'change',
            this.onFormChange.bind(this),
        );

        this.$emitter.subscribe(
            SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FINISHED,
            this.onFormChange.bind(this),
        );

        this.$emitter.subscribe(
            SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_REMOVED,
            this.onFormChange.bind(this),
        );

        const inputFields = DomAccess.querySelectorAll(this.buyForm, this.options.inputFieldsSelector, false);

        if (!inputFields) {
            this.onFormChange();
            return;
        }

        inputFields.forEach((field) => {
            field.addEventListener(
                'invalid',
                this._onInputInvalid.bind(this),
            );
        });

        // Initially check for required fields
        this.onFormChange();
    }

    /**
     * Find and validate a file-upload. Returns null if the field is no file-upload
     *
     * @param element
     * @return {null|boolean}
     */
    validateFileUpload(element) {
        const fileUploadContainer = element.closest(this.options.selectors.fileUploadContainer);
        if (!fileUploadContainer) {
            return null;
        }

        const fileUploadPlugin = window.PluginManager.getPluginInstanceFromElement(
            fileUploadContainer,
            'SwagCustomizedProductsFileUpload',
        );

        if (!fileUploadPlugin || !fileUploadPlugin.registry) {
            return true;
        }

        const uploadedFilesHaveValues = fileUploadPlugin.registry && fileUploadPlugin.registry.size > 0;

        if (!uploadedFilesHaveValues && DomAccess.getDataAttribute(element, 'required', false)) {
            return false;
        }

        return Array.from(fileUploadPlugin.registry.values()).every(({ valid }) => valid);
    }

    /**
     * Validates if all required input fields are filled
     */
    onFormChange() {
        const buyButton = DomAccess.querySelector(this.buyForm, this.options.selectors.buyButton);
        const inputFields = DomAccess.querySelectorAll(this.buyForm, this.options.inputFieldsSelector, false);
        const selectionValidation = {};
        const radioValidation = {};

        let areFieldsValid = false;
        if (inputFields) {
            areFieldsValid = Array.from(inputFields).every(field => {
                // Collect validity information of every selection input group
                const selectionOptionId = field.dataset.swagCustomizedProductsSelectionRequired;
                if (selectionOptionId !== undefined) {
                    selectionValidation[selectionOptionId] = selectionValidation[selectionOptionId] || field.checked;
                    if (field.hasAttribute('required')) {
                        field.removeAttribute('required');
                    }
                }

                if (field.hasAttribute('data-date-picker') && field.hasAttribute('required')) {
                    return !!field.value;
                }

                const fileUploadValidation = this.validateFileUpload(field);
                if (fileUploadValidation !== null) {
                    return fileUploadValidation;
                }

                const fieldValidationPlugin = window.PluginManager.getPluginInstanceFromElement(
                    field,
                    'SwagCustomizedProductsFieldValidation',
                );

                if (fieldValidationPlugin) {
                    return fieldValidationPlugin.isValid();
                }

                if (field.type === 'radio' && field.hasAttribute('required')) {
                    const name = field.name;

                    // save the checked state of radio fields by name
                    radioValidation[name] = radioValidation[name] || field.checked;
                }

                // required selection field dropdown without a selected value
                return !(field.tagName.toLowerCase() === 'select' && field.hasAttribute('required') && field.value === '');
            });
        }

        let requiredFieldsFilled = areFieldsValid &&
            Object.values(selectionValidation).every(groupValid => groupValid) &&
            Object.values(radioValidation).every(nameValid => nameValid) &&
            this.exclusionsValid;

        const confirmInput = DomAccess.querySelector(this.buyForm, this.options.selectors.confirmInput, false);
        if (confirmInput) {
            requiredFieldsFilled = requiredFieldsFilled && confirmInput.checked;
        }

        if (requiredFieldsFilled) {
            if (buyButton.hasAttribute('disabled')) {
                buyButton.removeAttribute('disabled');
            }

            return;
        }

        buyButton.setAttribute('disabled', 'disabled');
    }

    _onInputInvalid(event) {
        const element = event.target.closest('.collapse');

        // NEW, see: https://getbootstrap.com/docs/5.0/components/collapse/#methods
        bootstrap.Collapse.getOrCreateInstance(element).show();

        this.$emitter.publish('invalid', {
            element: event.target,
        });
    }

    /**
     * Callback which updates the local property if the exclusions are valid
     *
     * @param {Event} event
     * @returns {void}
     */
    updateExclusionValidity(event) {
        this.exclusionsValid = !event.detail;
        this.onFormChange();
    }
}
