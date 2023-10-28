import type { PropType } from 'vue';
import template from './sw-text-field-ai.html.twig';
import './sw-text-field-ai.scss';

const { Component } = Shopware;
const { ShopwareError } = Shopware.Classes;

/**
 * @package content
 */
export default Component.wrapComponentConfig({
    template,

    props: {
        value: {
            type: String,
            required: false,
        },
        selectedText: {
            type: String,
            default: '',
        },
        error: {
            type: Object as PropType<ShopwareError>,
            default: null,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        isRetryAble: {
            type: Boolean,
            default: false,
        },
        isAutoFocus: {
            type: Boolean,
            default: true,
        }
    },

    data(): {
        currentValue: String,
        errorField: typeof ShopwareError,
    } {
        return {
            currentValue: this.value,
            errorField: this.error,
        }
    },

    watch: {
        value(value: string): void {
            this.currentValue = value;
        },
        error(error: typeof ShopwareError): void {
            this.errorField = error;
        }
    },

    methods: {
        onCancel(event: Event): void {
            event.stopPropagation();
            this.$emit('submit-cancel');
        },

        onRetry(): void {
            this.$emit('submit-retry');
        },

        onInput(value: string): void {
            if (this.errorField && value) {
                this.errorField = null;
            }
        }
    }
});
