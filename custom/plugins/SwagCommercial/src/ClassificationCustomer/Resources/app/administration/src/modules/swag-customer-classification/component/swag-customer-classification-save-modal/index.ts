/**
 * @package checkout
 */

import template from './swag-customer-classification-save-modal.html';
import './swag-customer-classification-save-modal.scss';

const { Component } = Shopware;

type ActionFunction = () => {};

interface ButtonConfig {
    action: string|ActionFunction,
    disabled?: boolean,
    position: 'left'|'right',
    label: string,
    key: string,
    variant?: string,
}

export default Component.wrapComponentConfig({
    template,

    props: {
        isLoading: {
            required: true,
            type: Boolean,
        },
        processStatus: {
            required: true,
            type: String,
        },
        itemTotal: {
            required: true,
            type: Number,
        },
    },

    data():{
        title: string,
        buttonConfig: ButtonConfig[],
    } {
        return {
            title: null,
            buttonConfig: [],
        };
    },

    computed: {
        currentStep(): string {
            if (this.isLoading && !this.processStatus) {
                return 'process';
            }

            if (!this.isLoading && this.processStatus === 'success') {
                return 'success';
            }

            if (!this.isLoading && this.processStatus === 'error') {
                return 'error';
            }

            return 'confirm';
        },

        buttons(): {right: ButtonConfig[], left: ButtonConfig[]} {
            return {
                right: this.buttonConfig.filter((button) => button.position === 'right'),
                left: this.buttonConfig.filter((button) => button.position === 'left'),
            };
        },
    },

    watch: {
        currentStep(value: string) {
            if (value === 'success') {
                this.redirect('success');
            }

            if (value === 'error') {
                this.redirect('error');
            }
        },
    },

    created(): void {
        this.createdComponent();
    },

    beforeDestroy(): void {
        this.beforeDestroyComponent();
    },

    methods: {
        createdComponent(): void {
            this.addEventListeners();
        },

        beforeDestroyComponent(): void {
            this.removeEventListeners();
        },

        addEventListeners(): void {
            window.addEventListener('beforeunload', (event) => this.beforeUnloadListener(event));
        },

        removeEventListeners(): void {
            window.removeEventListener('beforeunload', (event) => this.beforeUnloadListener(event));
        },

        beforeUnloadListener(event): void {
            if (!this.isLoading) {
                return '';
            }

            event.preventDefault();
            event.returnValue = this.$tc('swag-customer-classification.notificationModal.messageBeforeTabLeave');

            return this.$tc('swag-customer-classification.notificationModal.messageBeforeTabLeave');
        },

        onModalClose(): void {
            this.$emit('modal-close');
        },

        startClassify(): void {
            this.$emit('start-classify');
        },

        redirect(routeName: string): void {
            if (!routeName) {
                this.$emit('modal-close');
                return;
            }

            this.$router.push({ path: routeName });
        },

        setTitle(title: string): void {
            this.title = title;
        },

        updateButtons(buttonConfig: ButtonConfig): void {
            this.buttonConfig = buttonConfig;
        },

        onButtonClick(action: string|ActionFunction): void {
            if (typeof action === 'string') {
                this.redirect(action);
                return;
            }

            if (typeof action !== 'function') {
                return;
            }

            action.call();
        },
    }
});
