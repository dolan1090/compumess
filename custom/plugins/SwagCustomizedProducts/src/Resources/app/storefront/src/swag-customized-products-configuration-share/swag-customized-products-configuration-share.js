import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service.js';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

/**
 * Plugin to create a share of the current configuration
 *
 * @class
 */
export default class SwagCustomizedProductsConfigurationShare extends Plugin {
    /**
     * Plugin options
     *
     */
    static options = {
        /**
         * StoreAPI endpoint for creating a configuration and a share
         */
        url: '',

        /**
         * Holds the shop base url, used to generate the SEO url in the store api context
         */
        absoluteBaseUrl: '',

        /**
         * Holds the SalesChannel base url, used to generate the SEO url in the store api context
         */
        baseUrl: '',
    };

    /**
     * Initializes the plugin.
     *
     * @constructor
     * @returns {void}
     */
    init() {
        this.shareButton = this.el;
        this.buyForm = this.el.closest('form');
        this.iconPaperclip = DomAccess.querySelector(this.buyForm, '.swag-customized-products-share-icons .icon-paperclip');
        this.client = new HttpClient();

        this.registerEvents();
    }

    /**
     * Register all necessary event listeners
     *
     * @returns {void}
     */
    registerEvents() {
        this.shareButton.addEventListener(
            'click',
            this.onShareButtonClicked.bind(this),
        );
    }

    /**
     * Calls the store api to create a configuration share url
     *
     * @param event
     * @returns {void}
     */
    onShareButtonClicked(event) {
        // Prevents the buy form from being submitted
        event.preventDefault();

        const data = new FormData(this.buyForm);
        data.set('absoluteBaseUrl', this.options.absoluteBaseUrl);
        data.set('baseUrl', this.options.baseUrl);

        ElementLoadingIndicatorUtil.create(this.el.closest('.card'));

        this.client.post(this.options.url, data, this.onConfigurationReceived.bind(this));
    }

    /**
     * Replaces the share container content with a readonly input field containing the share url
     *
     * @param data
     * @returns {void}
     */
    onConfigurationReceived(data) {
        const shareUrl = JSON.parse(data).shareUrl;
        const container = this.el.parentNode;
        const inputGroup = document.createElement('div');
        inputGroup.classList.add('input-group');


        const urlInput = document.createElement('input');
        urlInput.setAttribute('readonly', true);
        urlInput.classList.add('form-control');
        urlInput.setAttribute('value', shareUrl);
        inputGroup.appendChild(urlInput);

        const appendFormDiv = document.createElement('div');

        const copyButton = document.createElement('button');
        copyButton.appendChild(this.iconPaperclip.cloneNode(true));

        ['btn', 'btn-sm'].forEach((classListElement) => {
            copyButton.classList.add(classListElement);
        });
        copyButton.addEventListener('click', (event) => {
            event.preventDefault();

            try {
                urlInput.select();
                urlInput.setSelectionRange(0, -1);

                document.execCommand('copy');
            } catch (e) {
                urlInput.classList.add('is-invalid');
            }

            urlInput.classList.add('is-valid');
        });

        appendFormDiv.appendChild(copyButton);
        inputGroup.appendChild(appendFormDiv);

        // Remove all children from the container
        Array.from(container.children).forEach((element) => {
            container.removeChild(element);
        });

        // Add the readonly input field containing the share url
        container.appendChild(inputGroup);

        ElementLoadingIndicatorUtil.remove(container.closest('.card'));
    }
}
