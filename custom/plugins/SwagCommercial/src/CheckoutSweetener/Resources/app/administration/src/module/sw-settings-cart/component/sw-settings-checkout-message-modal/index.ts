import template from './sw-settings-checkout-message-modal.html.twig';
import './sw-settings-checkout-message-modal.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-settings-checkout-message-modal', {
    template,

    inject: ['repositoryFactory', 'checkoutMessageService'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data(): {
        keywords: string[],
        maxCharacters: number;
        productIds: string[],
        disableGeneration: boolean;
        isLoading: boolean,
        previewMessage: string
    } {
        return {
            keywords: [],
            maxCharacters: null,
            productIds: [],
            disableGeneration: true,
            isLoading: false,
            previewMessage: '',
        }
    },

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },
        charactersCount() {
            const messageLength = this.previewMessage.length;
            return `${messageLength}/${this.maxCharacters}`;
        },
        isCharactersCountVisible() {
            return this.maxCharacters !== null;
        },
        isButtonDisabled() {
            const noProducts = this.productIds.length === 0;
            const noKeywords = this.keywords.length === 0;

            return this.disableGeneration || noProducts || noKeywords || this.isLoading;
        },
        keywordOptions() {
            return [
                { label: this.$tc('commercial.checkout-sweetener.keywordOptions.neutral'), value: "neutral" },
                { label: this.$tc('commercial.checkout-sweetener.keywordOptions.exciting'), value: "exciting" },
                { label: this.$tc('commercial.checkout-sweetener.keywordOptions.humorous'), value: "humorous" },
            ]
        }
    },

    watch: {
        productIds(newIds, oldIds) {
            if (oldIds !== newIds)
                this.disableGeneration = false;
        },

        maxCharacters(newMax, oldMax) {
            if (newMax !== oldMax)
                this.disableGeneration = false;
        },

        keywords(newKeywords, oldKeywords) {
            if (oldKeywords !== newKeywords)
                this.disableGeneration = false;
        }
    },

    methods: {
        onClose(): void {
            this.$emit('modal-close');
        },

        async generatePreview(): Promise<void> {
            this.isLoading = true;
            try {
                const payload = {
                    keywords: this.keywords,
                    productIds: this.productIds,
                    length: this.maxCharacters
                };

                this.currentProductIdsLength = payload.productIds.length;

                const response = await this.checkoutMessageService.generate(payload);

                if (!response.data.text) {
                    return;
                }

                this.disableGeneration = true;
                this.previewMessage = response.data.text;

                this.createNotificationSuccess({
                    message: this.$tc('commercial.checkout-sweetener.messageSuccess'),
                });
            } catch (_error) {
                this.createNotificationError({
                    message: this.$tc('commercial.checkout-sweetener.messageFailure'),
                });
            } finally {
                this.isLoading = false;
            }
        }
    }
});

