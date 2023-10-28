import template from './sw-cms-el-text.html.twig';
import './sw-cms-el-text.scss';

const { ShopwareError } = Shopware.Classes;
const { Component, Mixin } = Shopware;

interface ElementWithClass extends Element {
    closest(selector: string): Element | null;
}

/**
 * @private
 * @package content
 */
export default Component.wrapComponentConfig({
    template,

    inject: [
        'contentGenerationService',
    ],

    mixins: [Mixin.getByName('notification')],

    data(): {
        pointedElement: Element|null,
        prevPointedElement: Element|null,
        showPopupAI: Boolean,
        showModal: Boolean,
        promptText: String,
        selectedText: Selection|String|null,
        isCancel: Boolean,
        isLoading: Boolean,
        isRetryAble: Boolean,
        errorField: typeof ShopwareError,
        isMultipleSelection: Boolean,
        isMultipleClick: Boolean,
        isAICopilot: Boolean
    } {
        return {
            pointedElement: null,
            prevPointedElement: null,
            showPopupAI: false,
            showModal: false,
            promptText: '',
            selectedText: '',
            isCancel: false,
            isLoading: false,
            isRetryAble: false,
            errorField: null,
            isMultipleSelection: false,
            isMultipleClick: false,
            isAICopilot: true
        }
    },

    mounted(): void {
        this.mountedComponent();
    },

    beforeDestroy(): void {
        this.beforeDestroyComponent();
    },

    computed: {
        text(): string {
            return typeof this.selectedText === 'object' ? this.selectedText.toString() : this.selectedText;
        }
    },

    methods: {
        mountedComponent(): void {
            document.addEventListener('mouseup', this.onExtendedSelectionChange);
            this.$el.addEventListener('click', (event) => {
                if (this.showPopupAI) {
                    this.onExtendedSelectionChange(event);
                }
            })
        },

        beforeDestroyComponent(): void {
            document.removeEventListener('mouseup', this.onExtendedSelectionChange);
            this.$el.removeEventListener('click', this.onExtendedSelectionChange)
        },

        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        onMultipleClick(event: CustomEvent): void {
            if (this.selectedText) {
                this.isMultipleClick = event.detail >= 3;
            }
        },

        handleError(errorResponse: Error, messageKey: string): void {
            this.onCancel()

            this.errorField = new ShopwareError({
                ...errorResponse,
                detail: this.$tc(messageKey),
            });

        },

        async handleEditContent(): Promise<void> {
            const requestData = {
                input: this.text,
                instruction: this.promptText,
            }

            try {
                const res = await this.contentGenerationService.editContent(requestData);

                if (!this.isCancel) {
                    const parentPointedEl = this.pointedElement.parentNode;
                    this.pointedElement.outerHTML = res.data;

                    if (!this.isMultipleClick && !this.isMultipleSelection) {
                        parentPointedEl.innerHTML = parentPointedEl.innerHTML;
                    }

                    this.selectedText = '';
                    this.onClosePopup();
                }
            } catch (error) {
                this.handleError(error, 'error.edit');
            }
        },

        async handleGenerateContent(): Promise<void> {
            const requestData = {
                sentence: this.promptText,
            }

            try {
                const res = await this.contentGenerationService.generate(requestData);
                if (!this.isCancel) {
                    const newRange = new Range();

                    if (this.pointedElement.getAttribute('contenteditable')) {
                        this.emitChanges(res.data);
                        this.pointedElement.removeAttribute('placeholder');

                        newRange.selectNode(this.pointedElement);
                        this.pointedElement.focus();
                    } else {
                        const domParser = new DOMParser().parseFromString(res.data, "text/html");
                        const generatedContent = domParser.querySelectorAll('body > *');

                        // @ts-ignore
                        this.pointedElement.replaceWith(...generatedContent);

                        // Create a new range and selection that encompasses the new element
                        newRange.setStart(generatedContent[0], 0);
                        newRange.setEnd(generatedContent[generatedContent.length - 1], 1);
                    }

                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(newRange);
                    this.onClosePopup(newRange);
                }
            } catch (error) {
                this.handleError(error, 'error.generate');
            }
        },

        onSubmit(): void {
            if (!this.promptText) {
                return this.errorField = new ShopwareError({
                    code: 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                });
            }

            this.errorField = null
            this.isLoading = true;
            this.isRetryAble = false;
            this.isCancel = false;

            if (this.selectedText) {
                this.handleEditContent();
            } else {
                this.handleGenerateContent();
            }
        },

        onCancel(): void {
            this.isLoading = false;
            this.isRetryAble = false;
            this.isCancel = true;
        },

        handleUpdate(): void {
            this.$nextTick(() => {
                const textContainer = this.$el.querySelector('.sw-text-editor__content-editor');
                this.emitChanges(textContainer.innerHTML);
            });
        },

        handleGetPointedSelector(): Node {
            // Get the current selection
            const selection = window.getSelection();

            if (!selection.anchorNode) {
                return null;
            }

            const pointedElement = selection.anchorNode;

            // Check if the common ancestor node is an element node
            if (pointedElement.nodeType === Node.ELEMENT_NODE) {
                return pointedElement;
            }

            return pointedElement.parentNode;
        },

        onClosePopup(range: Range): void {
            this.promptText = '';
            this.showPopupAI = false;
            this.showModal = false;
            this.isCancel = true;
            this.isLoading = false;

            if (this.selectedText) {
                if (this.isMultipleSelection) {
                    this.pointedElement.outerHTML = this.pointedElement.innerHTML;
                } else {
                    const parentPointedEl = this.pointedElement.parentNode;
                    this.pointedElement.outerHTML = this.pointedElement.innerHTML;
                    parentPointedEl.innerHTML = parentPointedEl.innerHTML;
                }

                this.selectedText = '';
                this.isMultipleSelection = false;
                this.isMultipleClick = false;
                return this.handleUpdate();
            }

            if (!range) {
                const range = document.createRange();
                range.selectNodeContents(this.pointedElement);

                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }

            this.handleUpdate();
        },

        onCloseModal(): void {
            this.showModal = false;
            const refInput = this.$refs.popupAI.querySelector('input');
            refInput.focus();
        },

        handleScrollUp(node: HTMLElement): void {
            const cmsDetailWrapper = document.querySelector('.sw-cms-detail__stage');

            if (cmsDetailWrapper) {
                cmsDetailWrapper.scrollTop += node.offsetTop;
            }
        },

        onExtendedSelectionChange(event: MouseEvent): void {
            const isPopupAI = (event.target as ElementWithClass).closest('.sw-cms-el-text-ai__popup');

            if (this.showPopupAI && !isPopupAI) {

                if (!!this.promptText) {
                    this.showModal = true;
                    return;
                }

                return this.onClosePopup();
            }

            if (isPopupAI) return;

            this.prevPointedElement = this.pointedElement;
            this.pointedElement = this.handleGetPointedSelector();

            if (this.selectedText && event.code === "Space") {
                return;
            }

            if (event.type === 'mouseup' && !window.getSelection().isCollapsed) {
                this.selectedText = window.getSelection();
            } else {
                this.selectedText = '';
            }

            if (this.prevPointedElement && (this.prevPointedElement !== this.pointedElement)) {
                this.prevPointedElement.removeAttribute('placeholder');
            }

            if (this.pointedElement && this.pointedElement.textContent.length >= 1 && this.pointedElement.textContent.trim()) {
                this.pointedElement.removeAttribute('placeholder');
            }

            if (this.pointedElement && !this.pointedElement.textContent.length) {
                this.pointedElement.setAttribute('placeholder', this.$tc('sw-cms-generation.placeholder'));
            }

            if (this.pointedElement && this.pointedElement.getAttribute('placeholder') && event.code === "Space") {
                this.errorField = null;
                this.promptText = '';
                this.isCancel = false;
                this.pointedElement.innerHTML = '<br>';
                this.showPopupAI = true;

                this.$nextTick(() => {
                    const inputEl = this.$refs.popupAI.querySelector('input');

                    inputEl.focus();
                    this.$refs.popupAI.style.top = `${this.pointedElement.offsetTop}px`;

                    this.handleScrollUp(this.pointedElement);
                })
            }
        },

        onChangeInput(value: string): void {
            this.promptText = value;
        },

        handleIsInlineNode(node: Element): Boolean {
            if (node.nodeType === 3) {
                return true; // not an element node
            }

            const inlineElements = ['a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em', 'i', 'img', 'input', 'kbd', 'label', 'map', 'object', 'output', 'q', 'samp', 'script', 'select', 'small', 'span', 'strong', 'sub', 'sup', 'textarea', 'time', 'tt', 'var'];
            return inlineElements.includes(node.tagName.toLowerCase());
        },

        handleGetSelectedNodes(sel: Selection): Node[] {
            const range = sel.getRangeAt(0);

            const commonAncestor = range.commonAncestorContainer;
            const walker = document.createTreeWalker(commonAncestor, NodeFilter.SHOW_ELEMENT, {
                acceptNode: function(node) {
                    if (range.intersectsNode(node)) {
                        return NodeFilter.FILTER_ACCEPT;
                    } else {
                        return NodeFilter.FILTER_SKIP;
                    }
                }
            });

            const selectedNodes = [];
            while (walker.nextNode()) {
                if (walker.currentNode.tagName.match(/^(BR|LI|OL)$/) || this.handleIsInlineNode(walker.currentNode)) continue;

                selectedNodes.push(walker.currentNode);
            }

            return selectedNodes;
        },

        handleGetBlockNode(node: Element): Node {
            return this.handleIsInlineNode(node) ? this.handleGetBlockNode(node.parentNode) : node;
        },

        handleRemoveEmptyNeighborElement(node: Element): void {
            if (node.nextElementSibling && !node.nextElementSibling.textContent.trim()) {
                node.nextElementSibling.remove();
            }

            if (node.previousElementSibling && !node.previousElementSibling.textContent.trim()) {
                node.previousElementSibling.remove();
            }
        },

        onHighlightText(): void {
            const range = this.selectedText.getRangeAt(0);
            const startNode = this.handleGetBlockNode(range.startContainer.parentNode);
            const endNode = this.handleGetBlockNode(range.endContainer.parentNode);

            const span = document.createElement("span");
            span.className = "highlight";

            if (this.isMultipleClick) {
                const elementNode = this.handleGetBlockNode(this.selectedText.anchorNode);
                span.innerHTML = elementNode.innerHTML;
                elementNode.innerHTML = '';
                elementNode.appendChild(span);
            } else if (this.selectedText.anchorNode === this.selectedText.focusNode) {
                span.innerHTML = range.toString();
                range.surroundContents(span);
            } else if (startNode === endNode) {
                const newRange = new Range();
                newRange.setStart(range.startContainer, range.startOffset);
                newRange.setEnd(range.endContainer, range.endOffset);

                const contents = newRange.cloneContents();
                const fragment = document.createDocumentFragment();
                span.appendChild(contents);
                fragment.appendChild(span);
                newRange.deleteContents();
                newRange.insertNode(fragment);
            } else {
                this.isMultipleSelection = true;
                let selectedNodes = this.handleGetSelectedNodes(this.selectedText);

                for (let i = 0; i < selectedNodes.length; i++) {
                    let node = selectedNodes[i];
                    node.parentNode.insertBefore(span, node);
                    span.appendChild(node);
                }
            }

            this.handleRemoveEmptyNeighborElement(span);

            this.selectedText = span.innerHTML;
            this.pointedElement = span;
            this.showPopupAI = true;
            this.isCancel = false;
            this.errorField = null;

            this.$nextTick(() => {
                const inputEl = this.$refs.popupAI.querySelector('input');

                inputEl.focus();
                this.$refs.popupAI.style.top = `${span.offsetTop + span.offsetHeight}px`;

                this.handleScrollUp(span);
            });
        },
    },

    provide() {
        return {
            onHighlightText: this.onHighlightText,
            isAICopilot: this.isAICopilot
        }
    }
})
