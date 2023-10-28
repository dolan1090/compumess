import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SwagCustomizedProductsFieldValidation extends Plugin {
    isFieldValid = true;

    static options = {
        errorElementSelector: '.customized-products-error-subtitle',
        fieldType: null,
        onEvent: 'change',
        translations: {
            numberfield: {
                required: 'No snippet provided (required)',
                min: 'No snippet provided (min)',
                max: 'No snippet provided (max)',
            },
            textfield: {
                required: 'No snippet provided (required)',
                min: 'No snippet provided (min)',
                max: 'No snippet provided (max)',
            },
            fileupload: {
                required: 'No snippet provided (required)',
                fileCount: 'No snippet provided (fileCount)',
                fileType: 'No snippet provided (fileType)',
                fileSize: 'No snippet provided (fileSize)',
                generic: 'No snippet provided (generic)',
            },
        },

        // These functions validate the field types and return the error text or null if there is none
        validationFunctions: {
            /**
             * Validates the value of the provided number field and returns an error if there is one
             *
             * @param {HTMLElement} element
             * @returns {null|string}
             */
            numberfield(element) {
                const value = parseInt(element.value, 10);
                const min = parseInt(element.min, 10);
                const max = parseInt(element.max, 10);

                if (!Number.isInteger(value) && element.required) {
                    return this.options.translations.numberfield.required;
                }

                if (value < min) {
                    return this.options.translations.numberfield.min;
                }

                if (value > max) {
                    return this.options.translations.numberfield.max;
                }

                return null;
            },

            /**
             * Validates the value of the provided text field and returns an error if there is one
             *
             * @param {HTMLElement} element
             * @returns {null|string}
             */
            textfield(element) {
                const { value, required, minLength, maxLength } = element;

                if (!value.length && required) {
                    return this.options.translations.textfield.required;
                }

                if (minLength !== -1 && value.length < minLength) {
                    return this.options.translations.textfield.min;
                }

                if (maxLength !== -1 && value.length > maxLength) {
                    return this.options.translations.textfield.max;
                }

                return null;
            },
        },
    }

    init() {
        this.el.addEventListener(this.options.onEvent, this.validateField.bind(this));
        this.validateField(true);
    }

    /**
     * Validates the element value and returns if it is valid
     *
     * @returns {boolean}
     */
    isValid() {
        this.validateField();
        return this.isFieldValid;
    }

    /**
     * Determines the correct validation function and uses it to validate the element
     *
     * @param {boolean} triggerFormChange
     */
    validateField(triggerFormChange = false) {
        if (triggerFormChange) {
            const formValidator = window.PluginManager.getPluginInstanceFromElement(
                this.el.closest('.swag-customized-products'),
                'SwagCustomizedProductsFormValidator',
            );

            formValidator.onFormChange();
        }

        let { fieldType } = this.options;
        // Try to autodetect the field type by the input type
        if (!fieldType) {
            const elementType = this.el.getAttribute('type');

            if (elementType === 'number') {
                fieldType = 'numberfield';
            } else if (elementType === 'text') {
                fieldType = 'textfield';
            } else {
                throw new Error('Could not detect fieldtype by input type. Please provide the fieldType option');
            }
        }

        const validationFunction = this.options.validationFunctions[fieldType];

        if (!validationFunction) {
            throw new Error(`No validation function for fieldType "${fieldType}"`);
        }

        const validationError = validationFunction.call(this, this.el);

        // Field is valid
        if (validationError === null) {
            this.hideError();
            return;
        }

        this.showError(validationError);
    }

    getErrorElement() {
        return DomAccess.querySelector(this.el.parentElement, this.options.errorElementSelector);
    }

    hideError() {
        const errorElement = this.getErrorElement();

        errorElement.style.display = 'none';
        this.el.classList.remove('is-error');

        this.isFieldValid = true;
    }

    showError(errorText) {
        const errorElement = this.getErrorElement();

        errorElement.style.display = 'block';
        errorElement.innerText = errorText;
        this.el.classList.add('is-error');

        this.isFieldValid = false;
    }
}
