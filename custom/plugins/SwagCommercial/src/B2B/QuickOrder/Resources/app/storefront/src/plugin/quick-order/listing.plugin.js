/**
 * @package checkout
 */

import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import Iterator from 'src/helper/iterator.helper';
import PageLoadingIndicatorUtil from 'src/utility/loading-indicator/page-loading-indicator.util';

export default class B2BQuickOrderPlugin extends Plugin {
    static options = {
        productItemsList: '.quick-order-content-list',
        productItem: '.quick-order-line-item',
        emptyItemClassName: 'quick-order-empty-item',
        buttonConfirmAddToCart: '.btn-confirm-add-to-cart',
        buttonAddToCart: '.btn-add-to-cart',
        buttonRemoveList: '.btn-clear-list',
        buttonConfirmRemove: '.btn-confirm-remove',
        buttonUploadCSV: '.btn-csv-upload',
        addToCartModal: '.add-to-cart-modal',
        removeListModal: '.remove-list-modal',
        notFoundItemsAlert: '#notFoundItemsAlert',
        duplicatedItemAlert: '#duplicatedItemAlert',
        itemAction: '.quick-order-item-action',
        itemNameInput: '.input-product-name',
        itemQuantityInput: '.input-product-quantity',
        itemQuantityGroup: '.line-item-quantity-group',
    };

    init() {
        this._addToCartModal = this.el.querySelector(this.options.addToCartModal);
        this._buttonAddToCart = this.el.querySelector(this.options.buttonAddToCart);
        this._buttonConfirmAddToCart = this.el.querySelector(this.options.buttonConfirmAddToCart);

        this._removeListModal = this.el.querySelector(this.options.removeListModal);
        this._buttonRemoveList = this.el.querySelector(this.options.buttonRemoveList);
        this._buttonConfirmRemove = this.el.querySelector(this.options.buttonConfirmRemove);

        this._buttonUploadCSV = this.el.querySelector(this.options.buttonUploadCSV);

        // Store original empty row
        this._emptyRow = this.el.querySelector(`.${this.options.emptyItemClassName}`).cloneNode(true);
        this._notFoundItemsAlert = this.el.querySelector(this.options.notFoundItemsAlert).cloneNode(true);
        this._duplicatedItemAlert = this.el.querySelector(this.options.duplicatedItemAlert).cloneNode(true);

        this._client = new HttpClient();

        this._getBaseQuickOrder();
        this._registerEvents();
    }

    _registerEvents() {
        this._buttonConfirmAddToCart.addEventListener('click', this._handleAddToCart.bind(this));
        this._buttonConfirmRemove.addEventListener('click', this._clearList.bind(this));

        document.$emitter.subscribe('QuickOrder/onProductsLoaded', this._renderProductList.bind(this));

        this.$emitter.subscribe('QuickOrder/updateNotFoundProducts', this._showNotFoundItems.bind(this));
        this.$emitter.subscribe('QuickOrder/updateDuplicatedItem', this._showDuplicatedItem.bind(this));

        window.addEventListener('beforeunload', this._beforeUnloadListener.bind(this));
    }

    _beforeUnloadListener(event) {
        if (!this._baseQuickOrder.products.length) {
            return;
        }

        event.preventDefault();

        const message = this.options.reloadPageMessage;
        PageLoadingIndicatorUtil.remove();

        event.returnValue = message;
        return message;
    }

    _hideNotFoundItemsAlert() {
        const notFoundItemsAlert = document.querySelector(this.options.notFoundItemsAlert);

        if (notFoundItemsAlert && !notFoundItemsAlert.classList.contains('d-none')) {
            notFoundItemsAlert.classList.add('d-none');
        }
    }

    _hideDuplicatedItemAlert() {
        const duplicatedItemAlert = document.querySelector(this.options.duplicatedItemAlert);

        if (duplicatedItemAlert && !duplicatedItemAlert.classList.contains('d-none')) {
            duplicatedItemAlert.classList.add('d-none');
        }
    }

    _showNotFoundItems(event) {
        const notFoundProductNumbers = event.detail.notFoundProducts;

        if (!notFoundProductNumbers.length) {
            this._hideNotFoundItemsAlert();

            return;
        }

        let notFoundItemsAlert = document.querySelector(this.options.notFoundItemsAlert);

        if (!notFoundItemsAlert) {
            const quickOrderAlertContainer = document.querySelector('.quick-order-alert');
            quickOrderAlertContainer.appendChild(this._notFoundItemsAlert.cloneNode(true));
            notFoundItemsAlert = quickOrderAlertContainer.querySelector(this.options.notFoundItemsAlert);
        }

        notFoundItemsAlert.classList.remove('d-none');

        const notFoundProductNumbersList = notFoundItemsAlert.querySelector('.alert-content-content');
        notFoundProductNumbersList.textContent = notFoundProductNumbers.join(', ');

        notFoundItemsAlert.addEventListener('closed.bs.alert', () => {
            this._baseQuickOrder.updateNotFoundProducts([]);
        });
    }

    _showDuplicatedItem(event) {
        const item = event.detail.duplicatedItem;

        if (!item) {
            this._hideDuplicatedItemAlert();
            return;
        }

        let duplicatedItemAlert = document.querySelector(this.options.duplicatedItemAlert);

        if (!duplicatedItemAlert) {
            const quickOrderAlertContainer = document.querySelector('.quick-order-alert');
            quickOrderAlertContainer.appendChild(this._duplicatedItemAlert.cloneNode(true));
            duplicatedItemAlert = quickOrderAlertContainer.querySelector(this.options.duplicatedItemAlert);
        }

        duplicatedItemAlert.classList.remove('d-none');

        const duplicatedItemName = duplicatedItemAlert.querySelector('.alert-content-content');
        duplicatedItemName.textContent = item;

        duplicatedItemAlert.addEventListener('closed.bs.alert', () => {
            this._baseQuickOrder.updateDuplicatedItem('');
        });
    }

    _clearList() {
        this._handleRemoveList();
        this._hideNotFoundItemsAlert();
        this._hideDuplicatedItemAlert();
    }

    _handleRemoveList() {
        this._baseQuickOrder.resetProductList();
        bootstrap.Modal.getInstance(this._removeListModal).hide();
    }

    _handleAddToCart() {
        bootstrap.Modal.getInstance(this._addToCartModal).hide();
        this._confirmAddToCart();
    }

    _onAfterAjaxSubmit() {
        PageLoadingIndicatorUtil.remove();
        this._openOffCanvasCarts();
    }

    /**
     *
     * @private
     */
    _openOffCanvasCarts() {
        const offCanvasCartInstances = window.PluginManager.getPluginInstances('OffCanvasCart');
        Iterator.iterate(offCanvasCartInstances, instance => this._openOffCanvasCart(instance));
    }

    /**
     *
     * @param {OffCanvasCartPlugin} instance
     * @private
     */
    _openOffCanvasCart(instance) {
        instance.openOffCanvas(this.options.openCartOffCanvasUrl, false, () => {
            this.$emitter.publish('openOffCanvasCart');
        });
    }

    _convertArrayToObject() {
        const lineItems = {};
        this._baseQuickOrder.products.forEach(item => {
            lineItems[item.id] = {
                id: item.id,
                quantity: item.quantity,
                referencedId: item.id,
                stackable: true,
                removable: true,
                type: 'product',
            }
        });

        return lineItems;
    }

    _confirmAddToCart() {
        PageLoadingIndicatorUtil.create();

        const productItemPayload = this._convertArrayToObject();

        this._client.post(
            this.options.addToCartUrl,
            JSON.stringify({ lineItems: productItemPayload }),
            this._onAfterAjaxSubmit.bind(this)
        );
    }

    _renderProductList(event) {
        const products = event.detail.products;

        // Reset list
        const list = this.el.querySelector(this.options.productItemsList);
        list.innerHTML = '';

        // Reset empty row
        this._resetEmptyLine();

        if (products.length === 0) {
            this._buttonAddToCart.disabled = true;
            this._buttonRemoveList.disabled = true;
            window.PluginManager.initializePlugins();

            return;
        }

        // Loop the items and generate a list of elements
        products.map(item => {
            const row = this._emptyRow.cloneNode(true);
            row.classList.remove(this.options.emptyItemClassName);

            const productNameInput = row.querySelector(this.options.itemNameInput);
            const quantityInput = row.querySelector(this.options.itemQuantityInput);
            const quantityGroup = row.querySelector(this.options.itemQuantityGroup);
            const itemAction = row.querySelector(this.options.itemAction);

            itemAction.classList.remove('d-none');
            quantityGroup.removeAttribute('disabled');
            quantityInput.tabIndex = 0;

            productNameInput.innerHTML = `<strong>${item.productNumber}</strong> - ${item.name}`;
            quantityInput.value = item.quantity;

            row.setAttribute('data-id', item.id);
            quantityInput.setAttribute('min', item.minPurchase);
            quantityInput.setAttribute('max', item.calculatedMaxPurchase);
            quantityInput.setAttribute('step', item.purchaseSteps);

            list.appendChild(row);
        });

        window.PluginManager.initializePlugins();

        this._buttonAddToCart.disabled = false;
        this._buttonRemoveList.disabled = false;
    }

    _resetEmptyLine() {
        const currentEmptyRow = this.el.querySelector(`.${this.options.emptyItemClassName}`);
        currentEmptyRow.replaceWith(this._emptyRow.cloneNode(true));

        const newEmptyRowInput = this.el.querySelector(`.${this.options.emptyItemClassName} ${this.options.itemNameInput}`);
        newEmptyRowInput.focus();
    }

    _getBaseQuickOrder() {
        this._baseQuickOrder = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-b2b-base-quick-order="true"]'), 'B2bBaseQuickOrder');
    }
}
