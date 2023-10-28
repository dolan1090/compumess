import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class SubscriptionProductBox extends Plugin {
    static options = {
        /**
         * Selector of the order confirm form
         *
         * @type string
         */
        radioName: 'subscription-plan-option',

        /**
         * Selector of the one time buy widget form
         *
         * @type string
         */
        oneTimeBuyWidgetFormSelector: '#productDetailPageBuyProductForm',

        /**
         * Selector of the subscription buy widget
         *
         * @type string
         */
        subscriptionBuyWidgetSelector: '.subscription-product-box-buy-widget',

        /**
         * Selector of the subscription interval select dropdown
         *
         * @type string
         */
        subscriptionIntervalSelectSelector: '.subscription-product-box-select-options-interval-select',

        /**
         * Selector of the subscription minimum execution label
         *
         * @type string
         */
        subscriptionMinimumExecutionLabelSelector: '.subscription-minimum-execution-label',
    };

    init() {
        this._registerEvents();
        this._matchState();
        this._setRadioContentVisibility();
    }

    _registerEvents() {
        const options = this.el[this.options.radioName];
        options.forEach((option) => {
            option.addEventListener('change', this._onChange.bind(this));
        });
    }

    _onChange(event) {
        this._setBuyButtonVisibility(event.target.value);
        this._setRadioContentVisibility();
    }

    _matchState() {
        const options = this.el[this.options.radioName];
        options.forEach((option) => {
            if (option.checked) {
                this._setBuyButtonVisibility(option.value);
            }
        });
    }

    _setBuyButtonVisibility(value) {
        const subscriptionWidget = DomAccess.querySelector(this.el, this.options.subscriptionBuyWidgetSelector);
        const oneTimeForm = DomAccess.querySelector(this.el.parentElement, this.options.oneTimeBuyWidgetFormSelector);

        if (!value) {
            subscriptionWidget.classList.add('d-none');
            oneTimeForm.classList.remove('d-none');
        } else {
            subscriptionWidget.classList.remove('d-none');
            oneTimeForm.classList.add('d-none');
        }
    }

    _setRadioContentVisibility() {
        const radios = this.el[this.options.radioName];

        radios.forEach((radio) => {
            const select = radio.parentElement.querySelector(this.options.subscriptionIntervalSelectSelector);
            const label = radio.parentElement.querySelector(this.options.subscriptionMinimumExecutionLabelSelector);

            if (select) {
                select.classList.add('d-none');

                if (radio.checked) {
                    select.classList.remove('d-none');
                }
            }

            if (label) {
                label.classList.add('d-none');

                if (radio.checked) {
                    label.classList.remove('d-none');
                }
            }
        });
    }
}
