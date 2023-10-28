import template from './sw-text-editor-toolbar.html.twig';
import './sw-text-editor-toolbar.scss';

const { Component } = Shopware;

interface AIButton {
    type: String,
    title: String,
    name: String,
}

/**
 * @private
 * @package content
 */
export default Component.wrapComponentConfig({
    template,

    inject: {
        onHighlightText: {
            from: 'onHighlightText',
            default: () => {},
        },
        isAICopilot: {
            from: 'isAICopilot',
            default: null,
        }
    },

    data(): {
        closeToolbar: Boolean,
        AIButton: Array<AIButton>,
        isAICopilot: Boolean|null
    } {
        return {
            closeToolbar: false,
            AIButton: [{
                type: 'ai',
                title: this.$tc('sw-cms-generation.AIToggle'),
                name: this.$tc('sw-cms-generation.AITextButton')
            }],
            isAICopilot: this.isAICopilot??null
        }
    },

    methods: {
        getLicense(toggle: string): boolean {
            return Shopware.License.get(toggle);
        },

        onClickAIButton(): void {
            this.$el.style.visibility = 'hidden';
            this.onHighlightText();
        }
    }
})
