import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';
import Debouncer from 'src/helper/debouncer.helper';

import { SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD }
    from '../swag-customized-products-file-upload/swag-customized-products-file-upload.plugin';

export default class SwagCustomizedProductsStepByStepWizard extends Plugin {
    /**
     * Plugin options
     *
     * @type {{
     *          containerSelector: string,
     *          pageSelector: string,
     *          configureStepByStepSelector: string,
     *          pagerSelector: string,
     *          navigationSelector: string,
     *          formControlSelector: string,
     *          buyButtonSelector: string,
     *          nextButtonSelector: string,
     *          fileUploadSelector: string,
     *          fileUploadElementClass: string,
     *          fileUploadDataAttribute: string,
     *          fileUploadFilesSelector: string,
     *          dateTimeClass: string,
     *          scrollableClass: string,
     *          history: { enabled: boolean, hashPrefix: string },
     *          validation: { delay: int, disableBuyButtonOnInvalid: boolean },
     *          maxContentHeight: int
     * }}
     */
    static options = {
        containerSelector: '.swag-customized-products__scrollable',
        pageSelector: '.swag-customized-products__item',
        configureStepByStepSelector: '*[data-swag-customized-product-step-by-step-configure="true"]',
        pagerSelector: '.swag-customized-products__pager-holder',
        navigationSelector: '.swag-customized-products__navigation-holder',
        formControlSelector: '.swag-customized-products-form-control',
        buyButtonSelector: '#productDetailPageBuyProductForm .btn-buy',
        nextButtonSelector: '.swag-customized-products-pager__button.btn-next',
        fileUploadSelector: '.customized-products-upload',
        fileUploadElementClass: 'swag-customized-products-option-type-template-fileupload',
        fileUploadDataAttribute: 'swag-customized-products-file-upload-options',
        fileUploadFilesSelector: '.customized-products-upload-files',
        dateTimeClass: 'swag-customized-products-options-datetime',
        scrollableClass: 'is--scrollable',
        history: {
            enabled: true,
            hashPrefix: 'wizard-step-',
        },
        validation: {
            delay: 300,
            disableBuyButtonOnInvalid: true,
        },
        maxContentHeight: 500,
    };

    /**
     * Initializes the plugin & gets the necessary elements from the DOM
     *
     * @returns {void}
     */
    init() {
        this.translations = {
            btnPrev: DomAccess.getDataAttribute(
                this.el,
                'swag-customized-product-step-by-step-translation-btnprev',
            ),
            btnNext: DomAccess.getDataAttribute(
                this.el,
                'swag-customized-product-step-by-step-translation-btnnext',
            ),
            btnFinish: DomAccess.getDataAttribute(
                this.el,
                'swag-customized-product-step-by-step-translation-btnfinish',
            ),
            required: DomAccess.getDataAttribute(
                this.el,
                'swag-customized-product-step-by-step-translation-required',
            ),
        };

        this.containerEl = DomAccess.querySelector(this.el, this.options.containerSelector);
        this.buyButton = DomAccess.querySelector(document, this.options.buyButtonSelector);

        this.form = this.el.closest('form');

        // Setup pages (and associated variables)
        this.pages = DomAccess.querySelectorAll(this.el, this.options.pageSelector);
        this.pages = this.collectPages(this.pages);

        this.pagesCount = this.pages.length;
        this.currentPage = 1;

        // Get the configure elements
        this.configureElements = DomAccess.querySelectorAll(this.el, this.options.configureStepByStepSelector);

        // Setup pager element
        this.pagerEl = DomAccess.querySelector(this.el, this.options.pagerSelector);
        this.pagerEl.innerHTML = this.renderPager();

        // Set up navigation element
        this.navigationEntries = this.collectNavigationEntries(this.pages);
        this.navigationEl = DomAccess.querySelector(this.el, this.options.navigationSelector);
        this.navigationEl.innerHTML = this.renderNavigationSelection();

        // Enable the "start configuration" button
        Iterator.iterate(this.configureElements, (el) => {
            el.removeAttribute('disabled');
        });

        // History management
        if (this.options.history.enabled) {
            this.transitionToPage(1);
        }

        this._registerEvents();
    }

    /**
     * Sets up the necessary event listeners for the plugin to work properly
     *
     * @returns {Boolean}
     */
    _registerEvents() {
        Iterator.iterate(this.configureElements, (el) => {
            el.addEventListener('click', this.onClickStartButton.bind(this), false);
        });

        this.form.addEventListener('change', this.onFormChanged.bind(this));

        const fileUploads = DomAccess.querySelectorAll(this.form, `*[data-${this.options.fileUploadDataAttribute}]`, false);
        if (fileUploads.length > 0) {
            fileUploads.forEach((fileUpload) => {
                fileUpload.addEventListener(
                    SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_FINISHED,
                    this.onFormChanged.bind(this),
                );

                fileUpload.addEventListener(
                    SWAG_CUSTOMIZED_PRODUCTS_FILE_UPLOAD.EVENT.UPLOAD_REMOVED,
                    this.onFormChanged.bind(this),
                );
            });
        }

        // Setup delegate event handler for the pager buttons
        this.pagerEl.addEventListener('click', (event) => {
            event.preventDefault();

            if (event.target.matches('.btn-prev')) {
                this.prevPage();
            }

            if (event.target.matches('.btn-next')) {
                this.nextPage();
            }
        });

        // Setup event listener for the navigation element
        this.navigationEl.addEventListener('change', this.onNavigationEntry.bind(this));

        // Set up popstate listener to history back button support
        if (!SwagCustomizedProductsStepByStepWizard.isHistoryApiSupported() || !this.options.history.enabled) {
            return false;
        }

        window.addEventListener('popstate', this.onPopstate.bind(this));
        return true;
    }

    /**
     * Event handler will be triggered when the user changes the selection in the navigation element.
     *
     * @event change
     * @params {EventImpl} event
     * @returns {void}
     */
    onNavigationEntry(event) {
        if (!event.target.matches('.swag-customized-products-navigation')) {
            return;
        }
        const selectedValue = parseInt(event.target.options[event.target.selectedIndex].value, 10);
        this.transitionToPage(selectedValue + 1);
    }

    /**
     * Event handler which will trigger when the user presses the browser back button
     *
     * @event popstate
     * @returns {void}
     */
    onPopstate() {
        this.parseLocationHashOnAndJumpToPage();
    }

    /**
     * Event listener which will get fired when the user starts the step-by-step wizard.
     *
     * @event click
     * @params {EventImpl} event
     * @returns {void}
     */
    onClickStartButton(event) {
        event.preventDefault();

        const newPage = 2;
        this.transitionToPage(newPage);
    }

    /**
     * Event handler which will be fired when the user changes a form field within the buy form
     *
     * @returns {void}
     */
    onFormChanged() {
        this.pages = this.collectPages(DomAccess.querySelectorAll(this.el, this.options.pageSelector));
        this.renderPager();
    }

    /**
     * Renders a navigation select field which allows to quickly jump between the steps.
     *
     * @returns {string}
     */
    renderNavigationSelection() {
        /**
         * Renders a single option of the select box.
         *
         * @params {Object} entry
         * @returns {string}
         */
        const renderSelectOption = (entry) => {
            return `
                <option value="${entry.pageNum}"${this.currentPage - 1 === entry.pageNum ? ' selected="selected"' : ''}>
                    ${entry.pageNum} - ${entry.name} ${entry.required ? `(${this.translations.required})` : ''}
                </option>`;
        };

        const renderCurrentlySelectedText = () => {
            const entry = this.navigationEntries.find((navEntry) => {
                return this.currentPage - 1 === navEntry.pageNum;
            });

            if (!entry) {
                return '';
            }

            return `${entry.pageNum}. ${entry.name}`;
        };

        /* Defines if the navigation element should be displayed */
        const showNavigation = () => {
            return this.currentPage <= 1 || this.currentPage >= this.pagesCount;
        };

        this.navigationEl.style.display = (showNavigation() ? 'none' : 'block');
        /* eslint-disable max-len */
        return `
            <div class="form-group">
                <div class="swag-customized-products-navigation">
                    <select class="swag-customized-products-navigation" tabindex="-1">
                       ${this.navigationEntries.map(renderSelectOption)}
                    </select>
                    <span class="swag-customized-products-navigation__text">
                        ${renderCurrentlySelectedText()}
                    </span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink"
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        class="swag-customized-products-navigation__icon">
                        <defs>
                            <path
                                id="icons-small-arrow-small-down-a"
                                d="M5.70710678,6.29289322 C5.31658249,5.90236893 4.68341751,5.90236893 4.29289322,6.29289322 C3.90236893,6.68341751 3.90236893,7.31658249 4.29289322,7.70710678 L7.29289322,10.7071068 C7.68341751,11.0976311 8.31658249,11.0976311 8.70710678,10.7071068 L11.7071068,7.70710678 C12.0976311,7.31658249 12.0976311,6.68341751 11.7071068,6.29289322 C11.3165825,5.90236893 10.6834175,5.90236893 10.2928932,6.29289322 L8,8.58578644 L5.70710678,6.29289322 Z"/>
                        </defs>
                        <use
                            fill="#758CA3"
                            fill-rule="evenodd"
                            transform="matrix(-1 0 0 1 16 0)"
                            xlink:href="#icons-small-arrow-small-down-a"/>
                    </svg>
                </div>
            </div>
        `;
        /* eslint-enable max-len */
    }

    /**
     * Returns the template string of the pager, including navigation buttons
     *
     * @returns {String}
     */
    renderPager() {
        /** Should the pager be visible */
        const showPager = () => {
            return this.currentPage <= 1 || this.currentPage >= this.pagesCount;
        };

        /** Returns the disable attribute for the prev button */
        const disableBtnPrev = () => {
            return this.currentPage <= 1 ? ' disabled="disabled"' : '';
        };

        /** Returns the disable attribute for the next button */
        const disableBtnNext = () => {
            const currentPage = this.pages[this.currentPage - 1];
            const currentNextButton = DomAccess.querySelector(this.el, this.options.nextButtonSelector, false);

            if (!SwagCustomizedProductsStepByStepWizard.isPageValid(currentPage)) {
                if (currentNextButton) {
                    currentNextButton.setAttribute('disabled', 'disabled');
                }

                return ' disabled="disabled"';
            }

            if (currentNextButton) {
                currentNextButton.removeAttribute('disabled');
            }

            return this.currentPage >= this.pagesCount ? ' disabled="disabled"' : '';
        };

        /** Returns the pager display e.g. [n] / [n] */
        const pageDisplay = () => {
            return `${this.currentPage - 1} / ${this.pagesCount - 2}`;
        };

        /** Returns the button text for the next button */
        const btnNextText = () => {
            if ((this.currentPage - 1) >= (this.pagesCount - 2)) {
                return this.translations.btnFinish;
            }
            return this.translations.btnNext;
        };

        return `
            <div class="swag-customized-products-pager${showPager() ? ' d-none' : ''}">
                <button class="swag-customized-products-pager__button btn-prev btn btn-sm btn-outline-primary" tabindex="0"
                        ${disableBtnPrev()}>
                    ${this.translations.btnPrev}
                </button>

                <span class="swag-customized-products-pager__page-number">
                    ${pageDisplay()}
                </span>

                <button class="swag-customized-products-pager__button btn-next btn btn-sm btn-outline-primary" tabindex="0"
                        ${disableBtnNext()}>
                    ${btnNextText()}
                </button>
            </div>
        `;
    }

    /**
     * Collects the pages and information about the page for the step-by-step wizard.
     *
     * @params {NodeList} pages
     * @returns {{pageEl: unknown, name: (*|string|null), pageNum: number, required: boolean, formValidation: Object}[]}
     */
    collectPages(pages) {
        return Array.from(pages).map((page, pageNum) => {
            const name = DomAccess.getDataAttribute(page, 'name', false) || null;

            const formValidation = this.collectFormControlFromPage(page, true);
            let required = false;

            // When we're having a form element, get the "required" attributes from the DOM elements
            if (formValidation && formValidation.elements) {
                required = formValidation.elements.some((el) => {
                    const dataRequired = DomAccess.getDataAttribute(el, 'required', false);
                    return el.required || !!el.dataset.swagCustomizedProductsSelectionRequired || dataRequired;
                });
            }

            return {
                pageEl: page,
                pageNum: pageNum,
                name,
                required,
                formValidation,
            };
        });
    }

    /**
     * Collects the entries for the navigation element.
     *
     * @params {Array} pages
     * @returns {Array}
     */
    collectNavigationEntries(pages) {
        return pages.reduce((accumulator, page) => {
            if (!page.name) {
                return accumulator;
            }
            accumulator.push(page);
            return accumulator;
        }, []);
    }

    /**
     * Switches to the next step in the wizard process.
     *
     * @returns {Boolean}
     */
    nextPage() {
        let newPage = this.currentPage + 1;

        if (newPage >= this.pagesCount) {
            newPage = this.pagesCount;
        }

        return this.transitionToPage(newPage);
    }

    /**
     * Switches to the previous step in the wizard process.
     *
     * @returns {Boolean}
     */
    prevPage() {
        let newPage = this.currentPage - 1;

        if (newPage < 1) {
            newPage = 1;
        }

        return this.transitionToPage(newPage);
    }

    /**
     * Resets the wizard to the first page
     *
     * @returns {Boolean}
     */
    resetToFirstPage() {
        const newPage = 1;

        return this.transitionToPage(newPage);
    }

    /**
     * Transitions to the given page and updates the current page as well as the pager.
     *
     * @params {Number} newPage
     * @params {Boolean} [setHistoryEntry=true]
     * @returns {boolean}
     */
    transitionToPage(newPage, setHistoryEntry = true) {
        this.resetPreviousFormControl(this.pages[this.currentPage - 1]);

        // Transition to the next page
        this.currentPage = newPage;

        this.setActiveFormElement(this.pages[this.currentPage - 1]);

        // Render pager & navigation element
        this.pagerEl.innerHTML = this.renderPager();
        this.navigationEl.innerHTML = this.renderNavigationSelection();


        // hide all pages
        this.pages.forEach(e => {
            e.pageEl.classList.add("swag-customized-products_step-mode-hidden");
            e.pageEl.classList.remove("swag-customized-products_step-mode-shown")
        })

        // set current page visible
        this.pages[newPage - 1].pageEl.classList.remove("swag-customized-products_step-mode-hidden");
        this.pages[newPage - 1].pageEl.classList.add("swag-customized-products_step-mode-shown")

        if (setHistoryEntry) {
            this.updateHistory();
        }

        return true;
    }

    setActiveFormElement(page) {
        const formValidation = page.formValidation;
        const { elements, handler } = formValidation;

        if (!elements || !handler) {
            return false;
        }

        elements.forEach((el) => {
            el.addEventListener('input', handler, false);
        });

        return true;
    }

    /**
     * Collects the form control element from the current page for validation purposes later on.
     *
     * @params {Number} pageNum
     * @returns {null|Object}
     */
    collectFormControlFromPage(page) {
        const formControlEl = Array.from(DomAccess.querySelectorAll(page, this.options.formControlSelector, false));

        // We're not having a form control element, so we reset it and the currentValidation object
        if (!formControlEl) {
            return {
                elements: null,
                handler: null,
                valid: true,
            };
        }

        // Set tab index for input elements on page to -1, so they're not focusable using the keyboard
        formControlEl.forEach((nonTabableEl) => {
            nonTabableEl.setAttribute('tabindex', -1);
        });

        const handler = Debouncer.debounce(
            this.validateCurrentField.bind(this),
            this.options.validation.delay,
        );

        return {
            elements: formControlEl,
            valid: formControlEl.every((el) => {
                // Select field validation
                if (el.dataset.swagCustomizedProductsSelectionRequired !== undefined) {
                    return el.checked;
                }

                // File- & ImageUpload validation
                if (el.classList.contains(this.options.fileUploadElementClass)) {
                    const fileUploadParent = el.closest(this.options.fileUploadSelector);
                    const maxCount = DomAccess.getDataAttribute(fileUploadParent, this.options.fileUploadDataAttribute)
                        .maxCount;
                    const fileCount = DomAccess.querySelector(fileUploadParent, this.options.fileUploadFilesSelector)
                        .children.length;
                    return fileCount > 0 && fileCount <= maxCount;
                }

                // DateTime field validation
                if (el.classList.contains(this.options.dateTimeClass)) {
                    return !!el.value;
                }

                // Fields, where no explicit validation is necessary
                return el.validity.valid;
            }),
            handler,
        };
    }

    /**
     * Resets the current validation object back to default state and removes the event handler from the el
     *
     * @returns {Boolean}
     */
    resetPreviousFormControl(page) {
        const { elements, handler } = page.formValidation;

        // Remove listener from element
        if (!handler) {
            return true;
        }

        elements.forEach((el) => {
            el.removeEventListener('input', handler);
        });

        return true;
    }

    /**
     * Validates the current field and checks if the field is valid
     * @event input
     * @params event
     */
    validateCurrentField() {
        const currentPage = this.pages[this.currentPage - 1];
        const { elements } = currentPage.formValidation;

        let isValid;
        if (DomAccess.getDataAttribute(elements[0], 'swag-customized-products-selection-required', false) !== undefined) {
            isValid = elements.reduce((accumulator, el) => {
                if (accumulator) {
                    return accumulator;
                }

                return el.checked;
            }, false);
        } else {
            isValid = elements.reduce((accumulator, el) => {
                if (!accumulator) {
                    return accumulator;
                }

                let elementValid = el.checkValidity();

                // We have to check if we're dealing with a date picker
                if (Object.prototype.hasOwnProperty.call(el, '_flatpickr')) {
                    // eslint-disable-next-line
                    const datePicker = el._flatpickr;
                    elementValid = datePicker.selectedDates.length > 0;
                }

                // We're dealing with a HTML editor
                if (el.__plugins && el.__plugins.size > 0 && el.__plugins.has('SwagCustomizedProductsHtmlEditor')) {
                    const plugin = window.PluginManager.getPluginInstanceFromElement(el, 'SwagCustomizedProductsHtmlEditor');
                    isValid = !plugin.isEmpty();
                }

                accumulator = elementValid;
                return accumulator;
            }, true);
        }

        currentPage.formValidation.valid = isValid;

        // Re-render the pager to update the buttons
        this.pagerEl.innerHTML = this.renderPager();
        this.navigationEl.innerHTML = this.renderNavigationSelection();
    }

    /**
     * Parses the initial hash on page load and jumps to the right step in the wizard
     *
     * @returns {Boolean}
     */
    parseLocationHashOnAndJumpToPage() {
        if (!SwagCustomizedProductsStepByStepWizard.isHistoryApiSupported() || !this.options.history.enabled) {
            return false;
        }

        if (!window.location.hash || window.location.hash.length <= 0) {
            return false;
        }

        const hash = window.location.hash.substring(1);
        let page = parseInt(hash.replace(this.options.history.hashPrefix, ''), 10);
        page += 1;

        this.transitionToPage(page, false);

        return true;
    }

    /**
     * Updates the URL on the client an adds the hash of the current step to it as well as updating the user
     * history.
     *
     * @returns {Boolean}
     */
    updateHistory() {
        if (!SwagCustomizedProductsStepByStepWizard.isHistoryApiSupported() || !this.options.history.enabled) {
            return false;
        }

        window.history.pushState({
            currentPage: this.currentPage,
            pagesCount: this.pagesCount,
        }, '', `#${this.options.history.hashPrefix}${this.currentPage - 1}`);

        return true;
    }

    /**
     * Iterates over the pages and validates if the entire step-by-step wizard is valid.
     *
     * @returns {boolean}
     */
    isValidConfiguration() {
        let isValid = this.pages.reduce((accumulator, page) => {
            // One of the fields is required and not valid, we always want to return false
            if (!accumulator || !page.required) {
                return accumulator;
            }

            accumulator = SwagCustomizedProductsStepByStepWizard.isPageValid(page);

            return accumulator;
        }, true);

        const exclusionListValidationPlugin = window.PluginManager.getPluginInstanceFromElement(
            this.el,
            'SwagCustomizedProductsExclusionListValidation',
        );

        if (exclusionListValidationPlugin) {
            isValid = !exclusionListValidationPlugin.isViolation;
        }

        return isValid;
    }

    /**
     * Validates if the provided page object is valid.
     *
     * @static
     * @param {Object} page
     * @returns {Boolean}
     */
    static isPageValid(page) {
        const { required, formValidation } = page;

        // We don't have any form elements, therefore we're always valid
        if (!formValidation) {
            return true;
        }

        // Field is required and not valid
        if (required && !formValidation.valid) {
            return false;
        }

        return true;
    }

    /**
     * Returns the element height of a given element including padding and margin.
     *
     * @static
     * @param {HTMLElement} el
     * @returns {Number}
     */
    static elementOuterHeight(el) {
        const rect = el.getBoundingClientRect();
        let height = rect.height;

        const style = getComputedStyle(el);

        height += parseInt(style.marginTop, 10) + parseInt(style.marginBottom, 10);
        return height;
    }

    /**
     * Returns if the current browser supports the history api and the replaceState method,
     * {@link see: https://developer.mozilla.org/en-US/docs/Web/API/History/replaceState}
     *
     * @static
     * @returns {boolean}
     */
    static isHistoryApiSupported() {
        // eslint-disable-next-line no-restricted-globals
        return !!(window.history && history.pushState);
    }
}
