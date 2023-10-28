/**
 * @package checkout
 */

import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class B2BQuickOrderPaginationPlugin extends Plugin {
    static options = {
        pageFirstButton: '.page-first',
        pagePrevButton: '.page-prev',
        pageLastButton: '.page-last',
        pageNextButton: '.page-next',
    };

    init() {
        this._pageFirstButton = this.el.querySelector(this.options.pageFirstButton);
        this._pagePrevButton = this.el.querySelector(this.options.pagePrevButton);
        this._pageLastButton = this.el.querySelector(this.options.pageLastButton);
        this._pageNextButton = this.el.querySelector(this.options.pageNextButton);

        this._initButtons();
        this._getBaseQuickOrder();

        this._registerEvents();
    }

    _registerEvents() {
        document.$emitter.subscribe('QuickOrder/onProductsLoaded', this._renderPagination.bind(this));
    }

    _initButtons() {
        this.buttons = DomAccess.querySelectorAll(this.el, '.pagination input[type=radio]', false);
        this._registerButtonEvents();
    }

    /**
     * @private
     */
    _registerButtonEvents() {
        this.buttons.forEach((radio) => {
            radio.addEventListener('change', this._onChangePage.bind(this));
        });
    }

    /**
     * @private
     */
    _unregisterButtonEvents() {
        this.buttons.forEach((radio) => {
            radio.removeEventListener('change', this._onChangePage.bind(this));
        });
    }

    _onChangePage(event) {
        this._baseQuickOrder.updatePagination(parseInt(event.target.value, 10));
    }

    _renderPagination() {
        const totalPages = this._baseQuickOrder.getTotalPages();
        const currentPage = this._baseQuickOrder.page;

        if (totalPages < currentPage) {
            this._baseQuickOrder.updatePagination(totalPages);
            return;
        }

        if (totalPages <= 1) {
            this.el.classList.add('d-none');
            return;
        }

        this.el.classList.remove('d-none');

        const { start, end } = this._getStartEndPages(currentPage, totalPages);

        const isFirstPage = currentPage === 1;
        const isLastPage = currentPage === totalPages;

        this._setButtonValue(this._pagePrevButton, isFirstPage ? 1 : currentPage - 1);
        this._setButtonValue(this._pageNextButton, isLastPage ? totalPages : currentPage + 1);
        this._setButtonValue(this._pageLastButton, totalPages);

        this._enableButtons([
            this._pageFirstButton,
            this._pagePrevButton,
            this._pageLastButton,
            this._pageNextButton
        ]);

        if (isFirstPage) {
            this._disableButtons([this._pageFirstButton, this._pagePrevButton]);
        }

        if (isLastPage) {
            this._disableButtons([this._pageLastButton, this._pageNextButton]);
        }

        const pageList = this.el.querySelector('ul');

        // Remove current pagination and event listener
        pageList.innerHTML = '';
        this._unregisterButtonEvents();

        pageList.insertAdjacentHTML('beforeend', this._pageFirstButton.outerHTML);
        pageList.insertAdjacentHTML('beforeend', this._pagePrevButton.outerHTML);

        for (let i = start; i <= end; i++) {
            const isActive = i === currentPage;

            const template = `
                <li class="page-item page-number ${isActive ? 'active' : ''}">
                    <input type="radio"
                           name="p"
                           id="p${i}"
                           value="${i}"
                           class="d-none"
                           title="pagination"
                           ${isActive ? 'checked' : ''}>
                    <label class="page-link" for="p${i}">${i}</label>
                </li>
            `;

            pageList.insertAdjacentHTML('beforeend', template);
        }

        pageList.insertAdjacentHTML('beforeend', this._pageNextButton.outerHTML);
        pageList.insertAdjacentHTML('beforeend', this._pageLastButton.outerHTML);

        this._initButtons();
    }

    _disableButtons(buttons) {
        buttons.forEach((button) => {
            button.setAttribute('disabled', 'disabled');
            button.querySelector('input').setAttribute('disabled', 'disabled');
        });
    }

    _enableButtons(buttons) {
        buttons.forEach((button) => {
            button.removeAttribute('disabled');
            button.querySelector('input').removeAttribute('disabled');
        });
    }

    _setButtonValue(button, value) {
        button.querySelector('input').value = value;
    }

    /**
     * @private
     */
    _getStartEndPages(currentPage, totalPages) {
        let start = currentPage - 2;

        if (start <= 0) {
            start = currentPage - 1;
        }

        if (start <= 0) {
            start = currentPage;
        }

        let end = start + 4;

        if (end > totalPages) {
            end = totalPages;
        }

        return { start, end};
    }

    _getBaseQuickOrder() {
        this._baseQuickOrder = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-b2b-base-quick-order="true"]'), 'B2bBaseQuickOrder');
    }
}
