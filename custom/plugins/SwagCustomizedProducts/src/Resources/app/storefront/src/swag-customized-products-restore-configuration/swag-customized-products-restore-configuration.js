import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

/**
 * Plugin to restore a previous or shared configuration on the product detail page
 *
 * @class
 */
export default class SwagCustomizedProductsRestoreConfiguration extends Plugin {
    /**
     * Plugin options
     *
     */
    static options = {
        /**
         * Array with all configured fields.
         *
         * @type {Array}
         */
        configuration: [],

        /**
         * The old configuration hash that needs to get updated.
         * If this property is set we are dealing with a configuration edit.
         *
         * @type {string}
         */
        oldHash: '',

        /**
         * Holds the string to identify a option of the image upload type
         */
        imageUploadType: '',

        /**
         * Holds the string to identify a option of the image upload type
         */
        fileUploadType: '',

        /**
         * Holds the neccessary selectors for this plugin
         */
        selectors: {
            dropZone: '#customized-products-dropzone-',
        },
    };

    /**
     * Initializes the plugin.
     *
     * @constructor
     * @returns {void}
     */
    init() {
        this.buyForm = this.el.closest('form');

        // If we are dealing with a configuration edit and the previous quantity got passed, restore the previous quantity
        if (this.options.oldHash && this.options.oldHash.length > 0 && this.options.configuration.quantity) {
            this.restoreQuantity();
        }

        this.restoreUploadValues();
    }

    /**
     * Sets the quantity select to the value of the
     *
     * @returns {void}
     */
    restoreQuantity() {
        const quantitySelect = DomAccess.querySelector(this.buyForm, '.product-detail-quantity-select');

        quantitySelect.value = this.options.configuration.quantity;
    }

    /**
     * Restores the values of any upload type option
     *
     * @returns {void}
     */
    restoreUploadValues() {
        const uploadTypes = [this.options.imageUploadType, this.options.fileUploadType];

        Object.keys(this.options.configuration).forEach((key) => {
            const value = this.options.configuration[key];

            if (!value.type || !uploadTypes.includes(value.type)) {
                return;
            }

            this.restoreFiles(key, value.value);
        });
    }

    /**
     * Restores files for a upload option identified by the option id
     *
     * @param {string} id
     * @param {Array} files
     * @returns {boolean}
     */
    restoreFiles(id, files) {
        const dropZone = DomAccess.querySelector(this.buyForm, `${this.options.selectors.dropZone}${id}`, false);

        if (!dropZone) {
            return false;
        }

        const fileUploadPlugin = window.PluginManager.getPluginInstanceFromElement(
            dropZone.parentNode,
            'SwagCustomizedProductsFileUpload',
        );

        files.forEach((fileData) => {
            const fileName = fileData.filename;
            const file = new File([], fileName);

            fileUploadPlugin.registry.set(fileName, {
                file,
                element: fileUploadPlugin._appendNewFileElement(fileName),
                valid: true,
            });

            fileUploadPlugin._onUploadSuccess(fileName, { mediaId: fileData.mediaId });
        });

        return true;
    }
}
