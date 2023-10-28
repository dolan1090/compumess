import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';

/**
 * @type {{EVENT: {UPLOAD_FINISHED: string, UPLOAD_FAILED: string, UPLOAD_REMOVED: string}}}
 */
export const SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD = {
    EVENT: {
        UPLOAD_FINISHED: 'SwagCustomizedProductsFileUploadFinished',
        UPLOAD_FAILED: 'SwagCustomizedProductsFileUploadFailed',
        UPLOAD_REMOVED: 'SwagCustomizedProductsFileUploadRemoved',
    },
};

/**
 * File upload component with several style applications and drag & drop functionality, using the
 * `data-swag-customized-products-file-upload` attribute
 */
export default class SwagCustomizedProductsFileUpload extends Plugin {
    static options = {
        /**
         * The endpoint to upload to.
         *
         * @type {String}
         */
        endpoint: '',

        /**
         * The id that will also be send to the backend.
         *
         * @type {String}
         */
        optionId: '',

        /**
         * Maximal upload amount of this option.
         *
         * @type {Number}
         */
        maxCount: 1,


        /**
         * Child selectors
         *
         * @type {Object}
         */
        selectors: {
            dropzonePrefix: '#customized-products-dropzone-',
            inputPrefix: '#customized-products-dropzone-input-',
            browseButtonPrefix: '#customized-products-browse-',
            dropzoneIdPrefix: '#customized-products-dropzone-',
            upload: '.customized-products-upload',
            buyForm: '#productDetailPageBuyProductForm',
            dropzone: '.customized-products-upload__dropzone',
            uploadedFilesList: '.customized-products-upload-files',
            fileTemplate: '.customized-products-upload-files-element',
            filename:
                '.customized-products-upload-files-element__container-filename',
            iconElement:
                '.customized-products-upload-files-element__container-icon',
            closeButton:
                '.customized-products-upload-files-element__container-close-button',
            priceDisplayContainer:
                '[data-swag-customized-product-price-display="true"]',
            customizedProductContainer: '.swag-customized-products',

            iconSuccess: '.customized-products-upload-icon-success',
            iconError: '.customized-products-upload-icon-error',
            errorText: '.customized-products-error-subtitle',
        },

        /**
         * Dynamically applied style classes
         *
         * @type {Object}
         */
        classes: {
            dragover: 'dragover',
            success: 'is--success',
            error: 'is--error',
        },
    };

    /**
     * Initialization of targeted elements and helpers
     */
    init() {
        this.fileUpload = this.el;

        // Check for the required template element
        if (this._prepareUploadedFilesList() === false) {
            return;
        }

        this.stepByStepElement = DomAccess.querySelector(
            document,
            '*[data-swag-customized-product-step-by-step="true"]',
            false,
        );

        this.dropzone = DomAccess.querySelector(
            this.fileUpload,
            this.options.selectors.dropzonePrefix + this.options.optionId,
        );

        this.input = DomAccess.querySelector(
            this.dropzone,
            this.options.selectors.inputPrefix + this.options.optionId,
        );

        this.browseButton = DomAccess.querySelector(
            this.dropzone,
            this.options.selectors.browseButtonPrefix + this.options.optionId,
        );

        this.buyForm = DomAccess.querySelector(
            document,
            this.options.selectors.buyForm,
        );

        this.iconSuccess = DomAccess.querySelector(
            this.fileUpload,
            this.options.selectors.iconSuccess,
        ).innerHTML;

        this.iconError = DomAccess.querySelector(
            this.fileUpload,
            this.options.selectors.iconError,
        ).innerHTML;

        this.httpClient = new HttpClient();
        this.registry = new Map();

        this._registerEventListeners();
    }

    /**
     * Initializes and prepares the file list elements
     *
     * @return {Boolean}
     *
     * @private
     */
    _prepareUploadedFilesList() {
        this.uploadedFilesList = DomAccess.querySelector(
            this.fileUpload,
            this.options.selectors.uploadedFilesList,
        );

        const fileTemplate = DomAccess.querySelector(
            this.uploadedFilesList,
            this.options.selectors.fileTemplate,
        );

        this.fileTemplate = fileTemplate.cloneNode(true);

        // Remove template node from DOM
        if (this.uploadedFilesList.hasChildNodes()) {
            this.uploadedFilesList.removeChild(fileTemplate);
        }

        return this.fileTemplate !== null;
    }

    /**
     * Registers event listeners of static elements
     *
     * @private
     */
    _registerEventListeners() {
        this.input.addEventListener(
            'change',
            this._onFileInputChanged.bind(this),
        );
        this.browseButton.addEventListener('click', this._onBrowse.bind(this));
        this.dropzone.addEventListener(
            'drop',
            this._onDropOnDropzone.bind(this),
        );

        document.addEventListener(
            'dragenter',
            this._onDragEnter.bind(this),
            false,
        );
        document.addEventListener(
            'dragleave',
            this._onDragLeave.bind(this),
            false,
        );
        document.addEventListener('dragover', this._onDragOver, false);
        document.addEventListener(
            'drop',
            this._onDropOnDocument.bind(this),
            false,
        );
    }

    /**
     * On drag enter of the dropzone and its children, specific styles and attributes will be applied
     *
     * @param {Object} event
     * @return {Boolean}
     *
     * @private
     */
    _onDragEnter(event) {
        event.preventDefault();
        const target = event.target;
        const dropzoneSelector = this.options.selectors.dropzoneIdPrefix + this.options.optionId;

        if (!target.closest(dropzoneSelector)) {
            return false;
        }

        this.dropzone.classList.add(this.options.classes.dragover);
        this.browseButton.setAttribute('disabled', 'disabled');

        return true;
    }

    /**
     * On drag leave of the dropzone or its children, the style and attribute changes of drag enter will be reverted
     *
     * @param {Object} event
     * @return {Boolean}
     *
     * @private
     */
    _onDragLeave(event) {
        const target = event.target;
        const dropzoneSelector =
            this.options.selectors.dropzoneIdPrefix + this.options.optionId;

        if (target.closest(dropzoneSelector)) {
            return false;
        }

        this.dropzone.classList.remove('dragover');
        this.browseButton.removeAttribute('disabled');

        return true;
    }

    /**
     * Prevents to replace the current page with an accidentally dropped file view
     *
     * @param {Object} event
     *
     * @private
     */
    _onDragOver(event) {
        event.preventDefault();
    }

    /**
     * On drop on dropzone and its children, the style and attribute changes of drag enter will be reverted
     *
     * @param {Object} event
     *
     * @private
     */
    _onDropOnDocument(event) {
        event.preventDefault();

        this.dropzone.classList.remove(this.options.classes.dragover);
        this.browseButton.removeAttribute('disabled');
    }

    /**
     * Opens the file dialog
     *
     * @param {Object} event
     *
     * @private
     */
    _onBrowse(event) {
        event.preventDefault();
        this.input.click();
    }

    /**
     * Handles files after drop
     *
     * @param {Object} event
     *
     * @private
     */
    _onDropOnDropzone(event) {
        this._onFilesAdded(event.dataTransfer.files);
    }

    /**
     * Handles files after choosing via file dialog
     *
     * @param {Object} event
     *
     * @private
     */
    _onFileInputChanged(event) {
        this._onFilesAdded(event.target.files);
    }

    /**
     * Handles multiple files to be uploaded
     *
     * @param {FileList} files
     * @returns {Boolean}
     * @private
     */
    _onFilesAdded(files) {
        Array.from(files).forEach(this._handleFileUpload.bind(this));
        this.input.value = '';
    }

    /**
     * Sets the page height in the step by step mode (requirement, the step by step mode is active).
     *
     * @returns {Boolean}
     */
    setPageHeightInStepByStep() {
        if (!this.stepByStepElement) {
            return false;
        }

        const plugin = window.PluginManager.getPluginInstanceFromElement(
            this.stepByStepElement,
            'SwagCustomizedProductsStepByStepWizard',
        );

        plugin.setPageHeight(plugin.currentPage, true);

        return true;
    }

    /**
     * Adds new UI element to the DOM, which represents an inserted file and registers the events of its results
     *
     * @param {File} file
     * @returns {Boolean}
     *
     * @private
     */
    _handleFileUpload(file) {
        if (this.registry.has(file.name)) {
            return false;
        }

        this.registry.set(file.name, {
            file,
            element: this._appendNewFileElement(file.name),
            valid: false,
            uploaded: false,
        });

        const maxFileCountExceeded = this.registry.size > this.options.maxCount;
        if (maxFileCountExceeded) {
            const ERROR_MAX_FILE_COUNT_EXCEEDED = '422';
            this._onUploadError(file.name, ERROR_MAX_FILE_COUNT_EXCEEDED);

            return false;
        }

        const fileReader = new FileReader();
        fileReader.addEventListener(
            'error',
            this._onUploadError.bind(this),
        );
        fileReader.addEventListener(
            'load',
            this._sendUploadRequest.bind(this, file),
        );
        fileReader.readAsArrayBuffer(file);

        return true;
    }

    /**
     * Adds a specific UI representation of a file to the DOM
     *
     * @param {String} filename
     *
     * @private
     */
    _appendNewFileElement(filename) {
        const fileElement = document.createElement('div');
        fileElement.setAttribute(
            'class',
            this.fileTemplate.getAttribute('class'),
        );
        fileElement.innerHTML = this.fileTemplate.innerHTML;

        const fileName = DomAccess.querySelector(
            fileElement,
            this.options.selectors.filename,
        );
        fileName.innerHTML = filename;

        this.uploadedFilesList.appendChild(fileElement);

        this.setPageHeightInStepByStep();

        return fileElement;
    }

    /**
     * Constructs a request payload for the upload request
     *
     * @param {File} file
     *
     * @private
     */
    _sendUploadRequest(file) {
        const requestPayload = new FormData();
        requestPayload.append('file', file);
        requestPayload.append('optionId', this.options.optionId);

        this.httpClient.post(
            this.options.endpoint,
            requestPayload,
            this._uploadRequestCallback.bind(this, file.name),
            'multipart/form-data',
            true,
        );
    }

    /**
     * Callback function, which calls further result handling
     *
     * @param {String} filename
     * @param {String} responseText
     * @param {XMLHttpRequest} request
     * @return {boolean}
     *
     * @private
     */
    _uploadRequestCallback(filename, responseText, request) {
        let responseData = {};
        try {
            responseData = JSON.parse(responseText);
        } catch (e) {
            this._onUploadError(filename, request.status.toString());

            return false;
        }

        // If the request resolves in error (e.g. max file site exceeded), error visualization will be called
        if (responseData.errors !== undefined) {
            this._onUploadError(filename, request.status.toString());
        } else {
            this._onUploadSuccess(filename, responseData);
        }

        this.updateBuyButton();

        return true;
    }

    /**
     * Changes the UI representation to its success state and adds the necessary information to the buyForm.
     * Also adds an EventListener to remove it.
     *
     * @param {String} filename
     * @param {Object} result
     * @event SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FINISHED
     *
     * @private
     */
    _onUploadSuccess(filename, result) {
        const file = this.registry.get(filename);
        // Due to this being an async callback, the file could be deleted already
        if (!file) {
            return;
        }

        const icon = DomAccess.querySelector(
            file.element,
            this.options.selectors.iconElement,
        );

        this.resetUploadElement(filename);

        file.valid = true;
        file.uploaded = true;

        file.element.classList.add(this.options.classes.success);

        icon.innerHTML = this.iconSuccess;

        const mediaIdInput = this._createMediaIdInput(filename, result.mediaId);
        const fileNameInput = this._createFileNameInput(filename);

        this.input.appendChild(mediaIdInput);
        this.input.appendChild(fileNameInput);

        Array.from(this.registry.values()).map((_file) => _file.file).forEach(this._handleFileUpload.bind(this));
        this.updatePriceDisplay();

        DomAccess.querySelector(
            file.element,
            this.options.selectors.closeButton,
        ).addEventListener(
            'click',
            this._onRemoveValidElement.bind(
                this,
                file,
                mediaIdInput,
                fileNameInput,
            ),
        );

        this.$emitter.publish(SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FINISHED);
    }


    /**
     * Removes styling classes and error text from the uploaded file
     *
     * @param {String} filename
     *
     * @returns Boolean
     */
    resetUploadElement(filename) {
        if (!this.registry.has(filename)) {
            return false;
        }
        const file = this.registry.get(filename);
        file.valid = false;

        const errorElement = DomAccess.querySelector(
            file.element,
            this.options.selectors.errorText,
        );

        errorElement.innerHTML = '';

        file.element.classList.remove(this.options.classes.success);
        file.element.classList.remove(this.options.classes.error);

        return true;
    }

    /**
     * Changes the UI representation to its success state and adds an EventListener to remove it.
     *
     * @param {String} filename
     * @param {string} responseStatusCode
     * @event SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FAILED
     *
     * @private
     */
    _onUploadError(filename, responseStatusCode) {
        const file = this.registry.get(filename);
        // Due to this being an async callback, the file could be deleted already
        if (!file) {
            return;
        }

        file.valid = false;
        file.uploaded = false;

        const icon = DomAccess.querySelector(
            file.element,
            this.options.selectors.iconElement,
        );

        file.element.classList.add(this.options.classes.error);
        icon.innerHTML = this.iconError;

        const errorElement = DomAccess.querySelector(
            file.element,
            this.options.selectors.errorText,
        );

        errorElement.innerHTML = this.getSnippetByErrorCode(responseStatusCode);

        DomAccess.querySelector(
            file.element,
            this.options.selectors.closeButton,
        ).addEventListener(
            'click',
            this._onRemoveInvalidElement.bind(this, file),
        );

        this.$emitter.publish(SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FAILED);
    }

    /**
     * Removes an uploaded file from the DOM and the buyForm
     *
     * @param {File} file
     * @param {Element} mediaIdInput
     * @param {Element} fileNameInput
     * @param {Object} event
     * @event SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_REMOVED
     *
     * @private
     */
    _onRemoveValidElement(file, mediaIdInput, fileNameInput, event) {
        this._removeElement(file, event);

        mediaIdInput.remove();
        fileNameInput.remove();

        this.updatePriceDisplay();
        this.updateBuyButton();
        this.triggerExclusionValidation();

        this.$emitter.publish(SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_REMOVED);
    }

    /**
     * Removes a failed upload UI representation from the DOM
     *
     * @param {File} file
     * @param {Object} event
     *
     * @private
     */
    _onRemoveInvalidElement(file, event) {
        this._removeElement(file, event);
        this.triggerExclusionValidation();
    }

    updateBuyButton() {
        const customizedProductContainer = DomAccess.querySelector(
            this.buyForm,
            this.options.selectors.customizedProductContainer,
            false,
        );

        if (customizedProductContainer) {
            const formValidatorPlugin = window.PluginManager.getPluginInstanceFromElement(
                customizedProductContainer,
                'SwagCustomizedProductsFormValidator',
            );

            formValidatorPlugin.onFormChange();
        }
    }

    /**
     * Removes the UI DOM element
     *
     * @param {File} file
     * @param {Object} event
     *
     * @private
     */
    _removeElement(file, event) {
        event.preventDefault();

        if (this.uploadedFilesList.contains(file.element)) {
            this.uploadedFilesList.removeChild(file.element);
        }

        this.registry.delete(file.file.name);

        const uploadedFilesCount = Array.from(this.registry.values()).filter(({ uploaded }) => uploaded).length;
        const shouldUploadFirstFile = uploadedFilesCount < this.options.maxCount;
        if (shouldUploadFirstFile) {
            const fileToUpload = Array.from(this.registry.values()).filter(({ uploaded }) => !uploaded);
            if (fileToUpload.length) {
                this._sendUploadRequest(fileToUpload[0].file);
            }
        }

        this.updateBuyButton();
        this.setPageHeightInStepByStep();
    }

    /**
     * Creates necessary media information for the buyForm
     *
     * @param {String} filename
     * @param {String} mediaId
     * @return {Element}
     *
     * @private
     */
    _createMediaIdInput(filename, mediaId) {
        const mediaIdInput = document.createElement('input');
        mediaIdInput.type = 'hidden';
        mediaIdInput.name = `customized-products-template[options][${this.options.optionId}][media][${filename}][id]`;
        mediaIdInput.value = mediaId;

        return mediaIdInput;
    }

    /**
     * Creates necessary file information for the buyForm
     *
     * @param {String} filename
     * @return {Element}
     *
     * @private
     */
    _createFileNameInput(filename) {
        const fileNameInput = document.createElement('input');
        fileNameInput.type = 'hidden';
        fileNameInput.name = `customized-products-template[options][${this.options.optionId}][media][${filename}][filename]`;
        fileNameInput.value = filename;

        return fileNameInput;
    }

    /**
     * Forces the price display to update
     *
     * @returns {boolean}
     */
    updatePriceDisplay() {
        const priceDisplayContainer = DomAccess.querySelector(
            document,
            this.options.selectors.priceDisplayContainer,
            false,
        );
        if (!priceDisplayContainer) {
            return false;
        }

        const priceDisplayPlugin = window.PluginManager.getPluginInstanceFromElement(
            priceDisplayContainer,
            'SwagCustomizedProductPriceDisplay',
        );
        priceDisplayPlugin.onFormChange();

        return true;
    }

    /**
     * Triggers the exclusion list validation
     * Returns true if triggering the validation was successful and false otherwise
     *
     * @returns {boolean}
     */
    triggerExclusionValidation() {
        const exclusionListContainer = DomAccess.querySelector(
            document,
            this.options.selectors.customizedProductContainer,
            false,
        );
        if (!exclusionListContainer) {
            return false;
        }

        const exclusionListPlugin = window.PluginManager.getPluginInstanceFromElement(
            exclusionListContainer,
            'SwagCustomizedProductsExclusionListValidation',
        );

        if (!exclusionListPlugin) {
            return false;
        }

        exclusionListPlugin.onInputChange();

        return true;
    }

    /**
     * Returns the corresponding snippet for the status code of the file upload
     *
     * @param responseStatusCode
     * @returns string
     */
    getSnippetByErrorCode(responseStatusCode) {
        const translations = DomAccess.getDataAttribute(
            this.el,
            'data-swag-customized-products-file-upload-translation-errors',
        );

        return translations[responseStatusCode] || translations.generic;
    }
}
