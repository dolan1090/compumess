/*
 * @package checkout
 */
import template from './sw-settings-cart-ai-card-preview-link.html.twig';
import './sw-settings-cart-ai-card-preview-link.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-settings-cart-ai-card-preview-link', {
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        characterLimit: {
            type: Number,
            require: true,
        },
        keywords: {
            type: Array,
            require: true,
            validator: (prop) => prop.every(keyword => typeof keyword === 'string')
        },
    },

    data(): {
        showPreviewModal: boolean,
    } {
        return {
            showPreviewModal: false,
        };
    },

    methods: {
        togglePreviewModal() {
            this.showPreviewModal = !this.showPreviewModal;
        }
    }
});
