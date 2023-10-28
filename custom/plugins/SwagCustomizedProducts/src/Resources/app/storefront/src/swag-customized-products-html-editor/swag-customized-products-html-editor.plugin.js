import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';

const possibleDefaultContent = [
    '<div><br></div>',
    '<p><br></p>',
    '<br>',
];

/**
 * Helper method which allows to create a new HTMLElement using a simple object. It allows setting classes, attributes,
 * html, children, listeners, styles and the value of the element.
 *
 * @param {String} [tag=div]
 * @param {Array<String>} [classes=[]]
 * @param {Object<String,String|Boolean>} [attributes={}]
 * @param {String} [html='']
 * @param {Array<HTMLElement>} [children=[]]
 * @param {Object<String,Function>} [listeners={}]
 * @param {Object<String,String>} [styles={}]
 * @param {String|null} [value=null]
 * @returns {HTMLElement}
 */
export const createElement = ({
    tag = 'div',
    classes = [],
    attributes = {},
    html = null,
    children = [],
    listeners = {},
    styles = {},
    value = null,
}) => {
    const element = document.createElement(tag);

    classes.forEach((className) => {
        element.classList.add(className);
    });

    Object.keys(attributes).forEach((key) => {
        const attributeValue = attributes[key];
        element.setAttribute(key, attributeValue);
    });

    Object.keys(styles).forEach((key) => {
        element.style[key] = styles[key];
    });

    if (html && html.length > 0) {
        element.innerHTML = html;
    }

    if (value && value.length < 0) {
        element.value = value;
    }

    children.forEach((child) => {
        element.appendChild(child);
    });

    Object.keys(listeners).forEach((key) => {
        const listener = listeners[key];
        element.addEventListener(key, listener, false);
    });

    return element;
};

/**
 * Helper method which provides support for IE 11. Unfortunately IE 11 doesn't support event construction. Instead we
 * have to use the document to create the event and initialize it afterwards.
 *
 * @param {String} eventName
 * @returns {Event}
 */
const createEvent = (eventName) => {
    if (typeof (Event) === 'function') {
        return new Event(eventName);
    }
    const event = document.createEvent('Event');
    event.initEvent(eventName, true, true);

    return event;
};

/**
 * Helper method which terminates if we're dealing with an Internet explorer
 *
 * @returns {Boolean}
 */
const isIE11 = () => {
    return /Trident/.test(navigator.userAgent);
};

/**
 * HTML editor component that is loaded when the 'data-swag-customized-products-html-editor' attribute exists.
 */
export default class SwagCustomizedProductsHtmlEditor extends Plugin {
    static options = {
        baseClass: 'swag-custommized-product-html-editor',
        customizedProductContainer: '.swag-customized-products',
        buttonConfig: [{
            action: 'bold',
            iconClassName: 'icon-editor-bold',
        }, {
            action: 'italic',
            iconClassName: 'icon-editor-italic',
        }, {
            action: 'underline',
            iconClassName: 'icon-editor-underline',
        }, {
            action: 'strikeThrough',
            iconClassName: 'icon-editor-strikethrough',
        }],
    };

    /**
     * Initializes the plugin, sets up the necessary elements and prepares the textarea.
     *
     * @constructor
     * @returns {void}
     */
    init() {
        const initialValue = this.el.value || '';

        /** @type {Object} */
        this.translations = DomAccess.getDataAttribute(this.el, 'swag-customized-products-html-editor-translations');

        /** @type {HTMLElement} */
        this.iconContainer = DomAccess.querySelector(document, this.options.iconContainerSelector);

        /** @type {SwagCustomizedProductsFormValidator} */
        this.formValidatorPlugin = window.PluginManager.getPluginInstanceFromElement(
            DomAccess.querySelector(document, this.options.customizedProductContainer),
            'SwagCustomizedProductsFormValidator',
        );
        /** @type {Array} */
        this.buttonConfig = this.hydrateButtonConfig(this.options.buttonConfig);

        // We don't need the icon container anymore
        this.iconContainer.parentNode.removeChild(this.iconContainer);
        delete this.iconContainer;

        /** @type {HTMLElement} */
        this.toolbar = this.createToolbarElement();

        /** @type {HTMLElement} */
        const { editor, editorWrapper } = this.createEditorElement(initialValue);
        this.editor = editor;
        this.editorWrapper = editorWrapper;

        /** @type {HTMLElement} */
        this.wrapper = this.createWrapperElement();

        /** @type {HTMLElement} */
        this.placeholder = this.createPlaceholder(initialValue.length <= 0);

        this.setupTextarea();
        this.createPanel();
    }

    /**
     * Hydrates the button configuration, adds the pressed state as well as the icon.
     *
     * @param {Array<Object>} buttonConfig
     * @returns {Array<Object>}
     */
    hydrateButtonConfig(buttonConfig) {
        return buttonConfig.map((config) => {
            const iconEl = DomAccess.querySelector(this.iconContainer, `.${config.iconClassName}`, false) || null;
            config.icon = null;
            config.label = this.translations[config.action];

            if (iconEl) {
                config.icon = iconEl.cloneNode(true);
            }

            return config;
        });
    }

    /**
     * Sets up the textarea and hides it visually.
     *
     * @returns {Boolean}
     */
    setupTextarea() {
        this.el.style.display = 'none';
        return true;
    }

    /**
     * Creates the editor panel.
     *
     * @returns {Boolean}
     */
    createPanel() {
        this.wrapper.appendChild(this.toolbar);
        this.wrapper.appendChild(this.editorWrapper);
        this.editorWrapper.appendChild(this.placeholder);

        this.el.parentNode.appendChild(this.wrapper);
        return true;
    }

    /**
     * Creates the toolbar element including the buttons.
     *
     * @returns {HTMLElement}
     */
    createToolbarElement() {
        const toolbar = createElement({
            classes: [
                this.getPrefixedClass('toolbar'),
            ],
            attributes: {
                role: 'toolbar',
                'aria-label': this.translations.textFormatting,
                'aria-controls': this.getEditorElementId(),
            },
        });

        this.buttonConfig.forEach((config) => {
            toolbar.appendChild(this.createToolbarButtonElement(config));
        });

        return toolbar;
    }

    /**
     * Creates toolbar buttons based on a given button configuration.
     *
     * @param {Object} buttonConfig
     * @returns {HTMLElement}
     */
    createToolbarButtonElement(buttonConfig) {
        const button = createElement({
            tag: 'button',
            classes: [
                this.getPrefixedClass('toolbar-button'),
                this.getPrefixedClass(`button-${buttonConfig.action}`, 'toolbar'),
            ],
            attributes: {
                'aria-pressed': buttonConfig.pressed,
                'aria-label': buttonConfig.label,
                tabindex: -1,
            },
            html: (!buttonConfig.icon ? buttonConfig.label : null),
            value: buttonConfig.label,
            listeners: {
                click: (event) => {
                    event.preventDefault();
                    this.onToolbarButtonPressed(buttonConfig);
                },
            },
        });

        if (buttonConfig.icon) {
            const icon = buttonConfig.icon;
            icon.setAttribute('aria-hidden', true);
            button.appendChild(icon);
        }

        buttonConfig.button = button;

        return button;
    }

    /**
     * Creates the wrapper element which contains the toolbar and editor
     *
     * @returns {HTMLElement}
     */
    createWrapperElement() {
        return createElement({
            classes: [
                this.options.baseClass,
            ],
        });
    }

    /**
     * Creates the editor element and sets up the necessary listener.
     *
     * @params {String} value
     * @returns {{editor: HTMLElement, editorWrapper: HTMLElement}}
     */
    createEditorElement(value) {
        const defaultContent = '<div><br></div>';
        // IE 11 doesn't support the `input event`, therefore we have to use the `keydown` event instead
        const inputEventName = isIE11() ? 'keydown' : 'input';
        let html = value || defaultContent;

        // Deactivate default content for IE 11
        if (isIE11() && !value.length) {
            html = '';
        }

        const editor = createElement({
            classes: [
                this.getPrefixedClass('editor'),
            ],
            attributes: {
                contenteditable: true,
                spellcheck: true,
                autocorrect: true,
                role: 'textbox',
                id: this.getEditorElementId(),
                'aria-multiline': true,
                tabindex: -1,
            },
            html: html,
            listeners: {
                keydown: (event) => {
                    if (!this.isEmpty() || event.keyCode !== 8) {
                        return;
                    }

                    event.preventDefault();
                    this.editor.innerHTML = defaultContent;
                },
                [inputEventName]: Debouncer.debounce(this.onEditorInput.bind(this), 350),
                focus: () => {
                    this.el.dispatchEvent(createEvent('focus'));
                    if (!this.isEmpty() || isIE11()) {
                        return;
                    }

                    this.editor.innerHTML = defaultContent;
                },
                blur: () => {
                    this.el.dispatchEvent(createEvent('blur'));
                    if (!this.isEmpty()) {
                        return;
                    }

                    this.showPlaceholder();
                },
            },
        });

        const editorWrapper = createElement({
            classes: [
                this.getPrefixedClass('editor-wrapper'),
            ],
        });
        editorWrapper.appendChild(editor);

        return { editor, editorWrapper };
    }

    /**
     * Creates placeholder element including event listeners.
     *
     * @param {Boolean} showPlaceholderOnStartup
     * @returns {HTMLElement}
     */
    createPlaceholder(showPlaceholderOnStartup) {
        const text = this.el.getAttribute('placeholder');

        return createElement({
            classes: [
                this.getPrefixedClass('placeholder'),
            ],
            styles: {
                display: (showPlaceholderOnStartup ? 'block' : 'none'),
            },
            listeners: {
                click: () => {
                    this.hidePlaceholder();
                    this.editor.focus();
                },
            },
            html: text,
        });
    }

    /**
     * Creates a class name based on the given arguments.
     *
     * @param {String} className
     * @param {String} [prefix=this.options.baseClass]
     * @returns {String|boolean}
     */
    getPrefixedClass(className, prefix = this.options.baseClass) {
        if (!className || !className.length < 0) {
            return false;
        }

        return `${prefix}__${className}`;
    }

    /**
     * Gets the ID from the editor element.
     *
     * @returns {string}
     */
    getEditorElementId() {
        return `${this.el.getAttribute('id')}-editor`;
    }

    /**
     * Gets the content from the editor.
     *
     * @returns {{enumerable: boolean}}
     */
    getContent() {
        return this.editor.innerHTML;
    }

    /**
     * Returns content of the editor element as text. Use {@link #getContent} if you want to have the html content.
     *
     * @returns {string}
     */
    getTextContent() {
        return this.editor.innerText.trim();
    }

    /**
     * Truthy if the editor element is empty, otherwise falsy.
     *
     * @returns {Boolean}
     */
    isEmpty() {
        return this.getTextContent().length <= 0;
    }

    /**
     * Event handler which gets called when the user enters content into the editor element.
     *
     * @event input
     * @returns {void}
     */
    onEditorInput() {
        const content = this.getContent();

        // Sync value back to the textarea
        this.el.value = content; // IE compatibility
        this.el.innerText = content; // Firefox compatibility
        this.el.innerHTML = content;

        if (possibleDefaultContent.includes(content)) {
            this.el.value = '';
        }

        // Fire change event for validation
        const changeEvent = createEvent('change');
        const inputEvent = createEvent('input');
        this.el.dispatchEvent(changeEvent);
        this.el.dispatchEvent(inputEvent);

        // Support for price display box
        this.el.closest('form').dispatchEvent(changeEvent);

        // Fire form validation plugin
        this.formValidatorPlugin.onFormChange();
    }

    /**
     * Event handler which gets fired when the user pressed a button in the toolbar.
     *
     * @param {Object} config
     */
    onToolbarButtonPressed(config) {
        const configIndex = this.buttonConfig.findIndex((buttonConfig) => {
            return buttonConfig.action === config.action;
        });

        document.execCommand(this.buttonConfig[configIndex].action, false);
    }

    /**
     * Shows placeholder element visually
     *
     * @returns {Boolean}
     */
    showPlaceholder() {
        if (!this.placeholder) {
            return false;
        }

        this.placeholder.style.display = 'block';

        return true;
    }

    /**
     * Shows placeholder element visually
     *
     * @returns {Boolean}
     */
    hidePlaceholder() {
        if (!this.placeholder) {
            return false;
        }

        this.placeholder.style.display = 'none';

        return true;
    }
}
