/**
 * @package checkout
 */

import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

const FILE_SIZE_LIMIT = 100 * 1024 * 1024; // 100MB in bytes

export default class B2bQuickOrderUploadModalPlugin extends Plugin {
    static options = {
        selectors: {
            fileInput: '.quick-order-upload__input',
            uploadArea: '.quick-order-upload',
            uploadElement: 'quick-order-upload__uploading',
            uploadContainer: '.quick-order-upload__container',
            modifierUploadState: 'quick-order-upload__container--uploading',
            modifierUploadSuccessState: 'quick-order-upload__container--success',
            messageElement: 'quick-order-upload__message',
            messageIcon: 'quick-order-upload__message-icon',
            messageText: 'quick-order-upload__message-text',
            messageErrorState: 'quick-order-upload__message--error',
            messageSuccessState: 'quick-order-upload__message--success',
            iconSuccess: '.quick-order-upload__icon-success',
            iconError: '.quick-order-upload__icon-error',
            selectButton: '.quick-order-upload__btn-select',
            uploadContent: '.quick-order-upload__content',
            removeButton: '.quick-order-upload__btn-remove',
            addButton: '.quick-order-upload__btn-add',
            applyButton: '.quick-order-upload__btn-apply',
            uploadModal: '#quickOrderUploadModal',
            uploadModalLabel: '#quickOrderUploadModalLabel',
            uploadOptions: '.quick-order-upload__options',
            uploadDuplicatedProducts: '.quick-order-upload__duplicated-products',
            uploadTable: '.quick-order-upload__table',
            uploadSelection: '.quick-order-upload__selection',
            uploadModalDuplicatedTitle: '.quick-order-upload__duplicated-title'
        },
    };

    init() {
        this.input = this.el.querySelector(this.options.selectors.fileInput);
        this.uploadContainer = this.el.querySelector(this.options.selectors.uploadContainer);
        this.uploadContent = this.el.querySelector(this.options.selectors.uploadContent);
        this.uploadArea = this.el.querySelector(this.options.selectors.uploadArea);
        this.addButton = this.el.querySelector(this.options.selectors.addButton);
        this.applyButton = this.el.querySelector(this.options.selectors.applyButton);
        this.messageElement = this.el.querySelector(`.${this.options.selectors.messageElement}`);
        this.iconSuccess =  this.el.querySelector(this.options.selectors.iconSuccess).innerHTML;
        this.iconError =  this.el.querySelector(this.options.selectors.iconError).innerHTML;
        this.uploadDuplicatedProducts = this.el.querySelector(this.options.selectors.uploadDuplicatedProducts);
        this.uploadOptions = this.el.querySelector(this.options.selectors.uploadOptions);
        this.uploadModalLabel = this.el.querySelector(this.options.selectors.uploadModalLabel);
        this.uploadTable = this.el.querySelector(this.options.selectors.uploadTable);
        this.uploadSelection = this.el.querySelector(this.options.selectors.uploadSelection);
        this.uploadModalDuplicatedTitle = this.el.querySelector(this.options.selectors.uploadModalDuplicatedTitle);
        this.httpClient = new HttpClient();
        this.isCancel = false;
        this.quantityMapping = [];

        this._getBaseQuickOrder();
        this._registerEvents();
    }

    _registerEvents() {
        this.removeButton = this.uploadArea.querySelector(this.options.selectors.removeButton);

        this.input.addEventListener(
            'change',
            this._onFileInputChanged.bind(this),
        );

        this.removeButton.addEventListener('click', this._removeFileUpload.bind(this));
        this.el.addEventListener('hide.bs.modal', this._closeModal.bind(this));
        this.el.addEventListener('show.bs.modal', this._openModal.bind(this));

        this.addButton.addEventListener('click', this._addProducts.bind(this));
        this.applyButton.addEventListener('click', this._finalResult.bind(this));
    }

    _removeFileUpload() {
        this.httpClient.abort();
        this.messageElement.innerHTML = '';
        this.uploadArea.hidden = false;

        this._endUpload();
    }

    _closeModal() {
        this.uploadModalDuplicatedTitle.hidden = true;
        this.uploadDuplicatedProducts.hidden = true;
        this.uploadOptions.hidden = true;
        this.applyButton.hidden = true;
        this.addButton.hidden = false;
        this.responseData = null;
        this.duplicatedProducts = null;
        this.checkedRadio = null;
        this.uploadSelection.innerHTML = '';
        const bodyTable = this.uploadTable.querySelector('tbody');
        const radio = this.uploadOptions.querySelector('input[type="radio"]');

        if (bodyTable) {
            this.uploadTable.removeChild(bodyTable);
        }

        if (radio) {
            radio.checked = true;
        }

        this._removeFileUpload();
    }

    _openModal() {
        if (this._baseQuickOrder.products.length > 0) {
            this.manualProducts = this._baseQuickOrder.products;
            this.uploadOptions.hidden = false;
        }
    }

    _onFileInputChanged(event) {
        this._startUpload();

        const file = event.target.files.length > 0
            ? event.target.files[0]
            : null;

        this._handleFileUpload(file);
        this.input.value = '';
    }

    _handleFileUpload(file = null) {
        if (!file || file.type !== 'text/csv' || file.size > FILE_SIZE_LIMIT) {
            return this._handleMessage(this.iconError, this._getSnippet('error'), this.options.selectors.messageErrorState);
        }

        this._successUpload(file);
    }

    _generateUploadElement(content) {
        const uploadElement = document.createElement('div');

        uploadElement.setAttribute(
            'class',
            this.options.selectors.uploadElement
        );

        uploadElement.innerHTML = content;

        return uploadElement;
    }

    _startUpload() {
        this.messageElement.innerHTML = '';
        this.messageElement.classList.forEach((className) => {
            if (className.includes(`${this.options.selectors.messageElement}--`)) {
                this.messageElement.classList.remove(className);
            }
        });

        if (this.uploadElement) {
            this.uploadContent.removeChild(this.uploadElement);
            this.uploadElement = null;
        }

        this.uploadElement = this._generateUploadElement(this._getSnippet('loading'));

        this.uploadContent.appendChild(this.uploadElement);
        this.uploadContainer.classList.add(this.options.selectors.modifierUploadState);
    }

    _endUpload() {
        this.addButton.disabled = true;
        this.input.disabled = false;

        if (this.uploadElement) {
            this.uploadContent.removeChild(this.uploadElement);
            this.uploadElement = null;
        }

        this.uploadContainer.classList.remove(this.options.selectors.modifierUploadState);
        this.uploadContainer.classList.remove(this.options.selectors.modifierUploadSuccessState);
    }

    _successUpload(file) {
        this._endUpload();

        this.uploadElement = this._generateUploadElement(file.name);
        this.fileUpload = file;
        this.input.disabled = true;
        this.addButton.disabled = false;

        this.uploadContent.appendChild(this.uploadElement);
        this.uploadContainer.classList.add(this.options.selectors.modifierUploadSuccessState);
    }

    _handleMessage(icon, text, modifier) {
        this._endUpload();

        const textElement = document.createElement('div');
        const iconElement = document.createElement('div');

        this.messageElement.classList.add(modifier);

        textElement.setAttribute(
            'class',
            this.options.selectors.messageText
        );
        textElement.textContent = text;

        iconElement.setAttribute(
            'class',
            this.options.selectors.messageIcon
        );
        iconElement.innerHTML = icon;

        this.messageElement.appendChild(iconElement);
        this.messageElement.appendChild(textElement);
    }

    _finalResult() {
        if (!this.uploadTable.tBodies.length) return false;
        this.responseData.quantityMapping = {};

        const rows = this.uploadTable.tBodies[0].rows;

        Array.from(rows).forEach(row => {
            const productNumber = row.querySelector('.quick-order-upload__product-number').textContent;
            const checkedQuantity = row.querySelector('.form-check-input:checked').value;

            this.responseData.quantityMapping[productNumber] = Number(checkedQuantity);
        });

        this.uploadCSVItem(this.responseData)
    }

    _findDuplicatedProducts(manualProducts, csvProducts) {
        const duplicatedProducts = [];

        manualProducts.forEach(item => {
            const productNumber = item.productNumber;

            if (productNumber in csvProducts) {
                duplicatedProducts.push({
                    productNumber: productNumber,
                    name: item.name,
                    csvQuantity: csvProducts[productNumber],
                    manualQuantity: parseInt(item.quantity)
                });
            }
        });

        return duplicatedProducts;
    }

    _createDuplicatedStructure(data) {
        // Create the table body
        const tbody = document.createElement('tbody');

        data.forEach(item => {
            const rowTemplate = `
                <tr>
                    <td>
                        <div class="quick-order-upload__product-name">
                            <span class="quick-order-upload__product-number">${item.productNumber}</span>
                            <span>${item.name}</span>
                        </div>
                    </td>
                    <td>${this._createRadioInput(item.productNumber, item.manualQuantity, 'manual', true)}</td>
                    <td>${this._createRadioInput(item.productNumber, item.csvQuantity,'csv')}</td>
                    <td>${this._createRadioInput(item.productNumber, item.csvQuantity + item.manualQuantity, 'combined')}</td>
                </tr>
            `;

            tbody.insertAdjacentHTML('beforeend', rowTemplate);

            const selectTemplate = `
                <label class="quick-order-upload__product-name">
                    <span class="quick-order-upload__product-number">${item.productNumber}</span>
                    <span>${item.name}</span>
                </label>
                <select name="${item.productNumber}" class="form-select">
                    <option value="${item.csvQuantity}" id="${item.productNumber}-csv">
                        ${this._getSnippet('csv') + ' (' + item.csvQuantity + ')'}
                    </option>
                    <option value="${item.csvQuantity + item.manualQuantity}" id="${item.productNumber}-combined">
                        ${this._getSnippet('combined')} (${item.csvQuantity + item.manualQuantity})
                    </option>
                    <option value="${item.manualQuantity}" id="${item.productNumber}-manual">
                        ${this._getSnippet('current') + ' (' + item.manualQuantity + ')'}
                    </option>
                </select>
            `;

            // Append label and select element to the container
            this.uploadSelection.insertAdjacentHTML('beforeend', selectTemplate);
        });

        this.uploadTable.appendChild(tbody);

        const quantityInputs = this.uploadTable.querySelectorAll('input[type="radio"]');
        Array.from(quantityInputs).forEach(input => {
            input.addEventListener('click', () => {
                // Find the corresponding option element in the selection div
                const optionElement = this.uploadSelection.querySelector(`select[name="${input.name}"] #${input.id}`);
                // Set the selected option of the select element to match the clicked radio button
                optionElement.selected = true;

            });
        });

        const quantitySelections = this.uploadSelection.querySelectorAll('select');
        Array.from(quantitySelections).forEach(select => {
            select.addEventListener('change', (event) => {
                const selectedOption = event.target.selectedOptions[0];
                const radioButton = this.uploadTable.querySelector(`input[type="radio"][id="${selectedOption.id}"]`);

                // Set the checked property of the radio button to true
                radioButton.checked = true;
            });
        });
    }

    _createRadioInput(productNumber, quantity, type, checked = false) {
        const id = `${productNumber}-${type}`;

        return `
            <div class="form-check">
                <input
                    type="radio"
                    class="form-check-input"
                    id="${id}"
                    name="${productNumber}"
                    value="${quantity}"
                    ${checked? 'checked' : ''}
                >
                <label class="form-check-label" for="${id}">${quantity}</label>
            </div>
        `;
    }

    _uploadRequestCallback(responseText) {
        let responseData = {};

        try {
            responseData = JSON.parse(responseText);
        } catch (e) {
            this._handleMessage(this.iconError, this._getSnippet('error'), this.options.selectors.messageErrorState);

            return false;
        }

        if (this.checkedRadio) {
            responseData.option = this.checkedRadio.value;
        }

        if (this.checkedRadio && this.checkedRadio.value === 'add') {
            this.quantityMapping = this._getProductNumberQuantityMapping(responseData.products);
            this.duplicatedProducts = this._findDuplicatedProducts(this.manualProducts, this.quantityMapping);
        }

        if (this.duplicatedProducts && this.duplicatedProducts.length > 0) {
            this.uploadArea.hidden = true;
            this.uploadOptions.hidden = true;
            this.addButton.hidden = true;
            this.uploadDuplicatedProducts.hidden = false;
            this.applyButton.hidden = false;
            this.uploadModalDuplicatedTitle.hidden = false;
            this.responseData = responseData;

            this._createDuplicatedStructure(this.duplicatedProducts);
            return;
        }

        this.uploadCSVItem(responseData);

        return true;
    }

    uploadCSVItem(response) {
        this.fileUpload = null;
        bootstrap.Modal.getInstance(this.el).hide();

        this._baseQuickOrder.handleUploadCSVItems(response);
    }

    _addProducts() {
        if (!this.uploadOptions.hidden) {
            const radios = this.uploadOptions.querySelectorAll('input[type="radio"]');

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    this.checkedRadio = radios[i];
                    break;
                }
            }
        }

        this.addButton.disabled = true;
        const requestPayload = new FormData();
        requestPayload.append('file', this.fileUpload);

        this.httpClient.post(
            '/account/quick-order/upload',
            requestPayload,
            this._uploadRequestCallback.bind(this),
        );
    }

    _getSnippet(responseStatus) {
        if (!this.options.snippets) {
            return '';
        }

        return this.options.snippets[responseStatus];
    }

    _getProductNumberQuantityMapping(products = []) {
        const mapping = {};

        products.forEach(product => {
            mapping[product.productNumber] = product.quantity;
        });

        return mapping;
    }

    _getBaseQuickOrder() {
        this._baseQuickOrder = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-b2b-base-quick-order="true"]'), 'B2bBaseQuickOrder');
    }
}
