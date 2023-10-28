/**
 * @package checkout
 */

import Plugin from 'src/plugin-system/plugin.class';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';
import LoadingIndicator from 'src/utility/loading-indicator/loading-indicator.util';
import Iterator from 'src/helper/iterator.helper';
import KeyboardNavigationHelper from '../../helper/keyboard-navigation.helper';

export default class B2BQuickOrderItemPlugin extends Plugin {
    static options = {
        itemNameInput: '.input-product-name',
        searchResultListClassName: 'js-quick-order-result',
        searchResultItemClassName: 'js-quick-order-result-item',
        removeItemButton: '.quick-order-remove-button',
        itemNameContainer: '.quick-order-item-name',
        itemQuantityInputClassName: 'input-product-quantity',
        itemLoader: '.quick-order-item-loader',
        searchWidgetDelay: 250,
    };

    init() {
        this._client = new HttpClient();
        this._selectedItem = null;

        this._buttonRemove = this.el.querySelector(this.options.removeItemButton);
        this._inputProductField = this.el.querySelector(this.options.itemNameInput);
        this._inputQuantityField = this.el.querySelector(`.${this.options.itemQuantityInputClassName}`);

        this._initializeData();
        this._getBaseQuickOrder();
        this._registerEvents();
    }

    _initializeData() {
        if (!this._inputProductField?.textContent) {
            return;
        }

        const productName = this._inputProductField?.textContent?.split(' - ');

        this._selectedItem = {};
        this._selectedItem.productNumber = productName[0];
        this._selectedItem.name = productName[1];
        this._selectedItem.id = this.el.getAttribute('data-id');
        this._handleLimitationQuantity(this._inputQuantityField);
    }

    _registerEvents() {
        // initialize the arrow navigation
        this._navigationHelper = new KeyboardNavigationHelper(
            this._inputProductField,
            `.${this.options.searchResultListClassName}`,
            `.${this.options.searchResultItemClassName}`,
            true,
        );

        // add listener to the input event
        this._inputProductField.addEventListener(
            'input',
            Debouncer.debounce(this._handleProductFieldChange.bind(this), this.options.searchWidgetDelay),
            {
                capture: true,
                passive: true,
            },
        );

        this._buttonRemove.addEventListener('click', this._handleRemoveItem.bind(this), false);
        this.el.addEventListener('change', this._handleQuantityChange.bind(this));

        // add click event listener to body
        document.body.addEventListener('click', this._onBackgroundClick.bind(this));
    }

    /**
     * Handle product search term change
     * @param {Event} event
     * @private
     */
    _handleProductFieldChange(event) {
        const eventTarget = event.target;
        const value = eventTarget.textContent.trim();

        if (!value) {
            return;
        }

        this.$emitter.publish('beforeSearch');
        this._handleSearchProduct(value, 1);
    }

    _handleSearchProduct(value, page = 1, scrollable = false) {
        let itemNameField = this.el.querySelector(this.options.itemNameContainer),
            url = this.options.searchProductUrl + '?search=' + encodeURIComponent(value);

        if (!scrollable) {
            this._clearSuggestResults();
        }

        url = `${url}&page=${page}`;

        // init loading indicator
        const productLoader = this.el.querySelector(this.options.itemLoader);
        const indicator = new LoadingIndicator(productLoader);
        indicator.create();

        this._client.abort();
        this._client.get(url, (response) => {
            if (!response) {
                return;
            }

            // remove indicator
            indicator.remove();

            const dataObj = JSON.parse(response),
                  products = dataObj.elements;

            let resultListUl = itemNameField.querySelector('ul');

            if (!resultListUl) {
                resultListUl = document.createElement('ul');
                resultListUl.classList.add(this.options.searchResultListClassName);
                // Add search result list below the product input field
                itemNameField.appendChild(resultListUl);
            }

            resultListUl.addEventListener('scroll', this._handleOnScroll.bind(this, dataObj, value));

            if (products.length === 0) {
                this._renderEmptyResult(itemNameField, resultListUl);
            } else {
                this._renderResultItem(products, resultListUl, value);
                this._registerEventSelectElement();
            }

            this.$emitter.publish('afterSuggest');
        });
    }

    _renderEmptyResult(item, resultListUl) {
        if (item.getElementsByTagName('li').length > 1) {
            return;
        }

        const resultItemTemplate = `
                <li class="no-product">${this.options.noProductFoundSnippet}</li>
            `;

        resultListUl.insertAdjacentHTML(
            'beforeend',
            resultItemTemplate
        );
    }

    _renderResultItem(products, resultListUl, value) {
        products.forEach((product) => {
            const productVariants = product.options;
            let productName = product.translated.name;
            let productNumber = product.productNumber;

            let re = new RegExp(value, 'i');

            if (productVariants.length > 0) {
                const optionsList = productVariants.map(item => {
                    return `${item?.group?.translated?.name}: ${item?.translated?.name}`;
                }).join(' | ');
                productName += ` (${optionsList})`;
            }

            if (re.test(productName)) {
                productName = productName.replace(re, '<span class="item-search-highlight">$&</span>');
            }

            if (re.test(productNumber)) {
                productNumber = productNumber.replace(re, '<span class="item-search-highlight">$&</span>');
            }

            const resultItemTemplate = `
                <li class="${this.options.searchResultItemClassName}"
                    data-min="${product.minPurchase}"
                    data-step="${product.purchaseSteps}"
                    data-id="${product.id}"
                    data-max="${product.calculatedMaxPurchase}"
                    tabindex="0"
                ><strong>${productNumber}</strong> - ${productName}</li>
            `;

            resultListUl.insertAdjacentHTML(
                'beforeend',
                resultItemTemplate
            );
        });
    }

    _handleOnScroll(dataObj, value, event) {
        let resultListUl = event.target;

        if (resultListUl.scrollTop + resultListUl.clientHeight !== resultListUl.scrollHeight ||
            resultListUl.getElementsByTagName('li').length === dataObj.total) {
            return;
        }

        this._handleSearchProduct(value, dataObj.page + 1, true);
    }

    _registerEventSelectElement() {
        const itemResultTriggers = document.querySelectorAll(`.${this.options.searchResultItemClassName}`);
        Iterator.iterate(itemResultTriggers, (item) => item.addEventListener('click', this._handleClickSelectElement.bind(this)));
    }

    /**
     * Clear product search result
     */
    _clearSuggestResults() {
        // reset arrow navigation helper
        this._navigationHelper.resetIterator();

        // remove all result popovers
        const results = document.querySelectorAll(`.${this.options.searchResultListClassName}`);
        Iterator.iterate(results, (result) => {
            result.removeEventListener('scroll', this._handleOnScroll.bind(this));
            result.remove();
        });

        this.$emitter.publish('clearSuggestResults');
    }

    /**
     * Click remove line item button
     * @private
     */
    _handleRemoveItem() {
        this._removeProduct(this._selectedItem.id);
    }

    /**
     * Close/remove the search results from DOM if user
     * clicks outside the form or the results popover
     * @param {Event} event
     * @private
     */
    _onBackgroundClick(event) {
        // early return if click target is the search result or any of it's children
        if (event.target.closest(`.${this.options.searchResultListClassName}`)) {
            return;
        }
        // remove existing search results popover
        this._clearSuggestResults();

        this.$emitter.publish('onBackgroundClick');
    }

    /**
     * Click on a product search result item
     * @param {Event} event
     * @private
     */
    _handleClickSelectElement(event) {
        const { searchResultItemClassName } = this.options;
        const eventTarget = event.target;
        const listItem = eventTarget.closest(`.${searchResultItemClassName}`);

        this._clearSuggestResults();

        if (!this._selectedItem) {
            this._selectedItem = {};
            this._updateSelectedItem(listItem);
            this._addNewProduct(this._selectedItem);
        } else {
            const oldItem = { ...this._selectedItem };
            this._updateSelectedItem(listItem);
            this._updateProduct({
                ...this._selectedItem,
                oldId: oldItem.id,
            });
        }
    }

    /**
     * Update selectedItem after select a product
     * @param {Element} searchListItem
     * @private
     */
    _updateSelectedItem(searchListItem) {
        this._updateQuantityInput(searchListItem);

        this._selectedItem.id = searchListItem.attributes['data-id'].value;
        const productName = searchListItem.textContent.split(' - ');
        this._selectedItem.productNumber = productName[0];
        this._selectedItem.name = productName[1];
    }

    /**
     * Update value of quantity field after changing product
     * @param {Element} selectedProductItem
     * @private
     */
    _updateQuantityInput(selectedProductItem) {
        if (selectedProductItem.hasAttribute('data-min')) {
            const minQuantity = selectedProductItem.getAttribute('data-min');
            this._inputQuantityField.setAttribute('min', minQuantity);
            this._selectedItem.minPurchase = parseInt(minQuantity, 10);
            this._inputQuantityField.value = this._inputQuantityField.value ?? minQuantity;
        }

        if (selectedProductItem.hasAttribute('data-step')) {
            const steps = selectedProductItem.getAttribute('data-step');
            this._inputQuantityField.setAttribute('step', steps);
            this._selectedItem.purchaseSteps = parseInt(steps, 10);
        }

        if (selectedProductItem.hasAttribute('data-max')) {
            const maxQuantity = selectedProductItem.getAttribute('data-max');
            this._inputQuantityField.setAttribute('max', maxQuantity);
            this._selectedItem.calculatedMaxPurchase = parseInt(maxQuantity, 10);
        }

        this._handleLimitationQuantity(this._inputQuantityField);
    }

    /**
     * Handle quantity input change
     * @param {Event} event
     * @private
     */
    _handleQuantityChange(event) {
        const eventTarget = event.target;

        if (!eventTarget.classList.contains(this.options.itemQuantityInputClassName)) {
            return;
        }

        this._handleLimitationQuantity(eventTarget);
        this._updateProduct(this._selectedItem);
    }

    /**
     * Handle quantity value by product quantity min, max and step
     * @param {Element} quantityInput
     * @private
     */
    _handleLimitationQuantity(quantityInput) {
        const quantityInputVal = parseInt(quantityInput.value);
        const min = parseInt(quantityInput.getAttribute('min'));
        const max = parseInt(quantityInput.getAttribute('max'));

        // Remove the disabled property of minus and plus button
        quantityInput.nextElementSibling.removeAttribute('disabled');
        quantityInput.previousElementSibling.removeAttribute('disabled');

        quantityInput.nextElementSibling.tabIndex = 0;
        quantityInput.previousElementSibling.tabIndex = 0;

        if (quantityInputVal >= max) {
            quantityInput.value = max;
            // Make plus button disabled
            quantityInput.nextElementSibling.setAttribute('disabled', 'disabled');
            quantityInput.nextElementSibling.tabIndex = -1;
        }

        if (quantityInputVal <= min) {
            quantityInput.value = min;
            // Make minus button disabled
            quantityInput.previousElementSibling.setAttribute('disabled', 'disabled');
            quantityInput.previousElementSibling.tabIndex = -1;
        }

        this._selectedItem.quantity = parseInt(quantityInput.value);
    }

    _addNewProduct(product) {
        this._baseQuickOrder.addProduct(product);
    }

    _updateProduct(product) {
        this._baseQuickOrder.updateProduct(product);
    }

    _removeProduct(id) {
        this._baseQuickOrder.removeProduct(id);
    }

    _getBaseQuickOrder() {
        this._baseQuickOrder = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-b2b-base-quick-order="true"]'), 'B2bBaseQuickOrder');
    }
}
