import DomAccess from 'src/helper/dom-access.helper';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import HttpClient from 'src/service/http-client.service';
import Plugin from 'src/plugin-system/plugin.class';

/**
 * @package checkout
 */
export default class CheckoutSweetenerPlugin extends Plugin {
    static options = {
        /**
         * The endpoint to call to get the sweetener
         *
         * @type string|null
         */
        endpoint: null,

        /**
         * The order to use to generate the sweetener
         *
         * @type string|null
         */
        orderId: null,
    };

    init() {
        if (!this.options.endpoint) {
            throw new Error('The endpoint option is required');
        }

        if (!this.options.orderId) {
            throw new Error('The orderId option is required');
        }

        this._client = new HttpClient();

        this._showLoader();

        this._client.post(this.options.endpoint, JSON.stringify({ 'orderId': this.options.orderId }), (text, response) => {
            if (response.status >= 400) {
                this._dismissLoader();
                return;
            }

            const sweetener = JSON.parse(text).text;

            this._replaceWithSweetener(sweetener);
            this._dismissLoader();
            this._showSweetenerDisclaimer();
        });
    }

    _replaceWithSweetener(sweetener) {
        DomAccess.querySelector(this.el, '.finish-sweetener-text').innerHTML = sweetener;
    }

    _showSweetenerDisclaimer() {
        DomAccess.querySelector(this.el, '.finish-sweetener-disclaimer', false)?.classList?.remove('d-none');
    }

    _showLoader() {
        this.el.classList.add('has--loader');
        ElementLoadingIndicatorUtil.create(this.el);
    }

    _dismissLoader() {
        ElementLoadingIndicatorUtil.remove(this.el);
        this.el.classList.remove('has--loader');
    }
}
