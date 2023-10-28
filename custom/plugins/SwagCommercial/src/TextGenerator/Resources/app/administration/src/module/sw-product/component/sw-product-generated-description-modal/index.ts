/*
 * @package inventory
 */

import template from './sw-product-generated-description-modal.html.twig';
import './sw-product-generated-description-modal.scss';

const { Component, Mixin } = Shopware;
const { mapState } = Shopware.Component.getComponentHelper();
const domUtils = Shopware.Utils.dom;

Component.register('sw-product-generated-description-modal', {
    template,

    inject: [
        'textGenerationService',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data(): {
        isLoading: boolean,
        keywords: string,
        selectedTone: string,
        generatedDescription: string,
    } {
        return {
            isLoading: false,
            keywords: '',
            selectedTone: '',
            generatedDescription: '',
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'apiContext',
            'product',
            'parentProduct',
        ]),
    },

    methods: {
        getLicense(toggle): boolean {
            return Shopware.License.get(toggle);
        },

        async generateText(): Promise<void> {
            this.isLoading = true;

            const productData = {
                keywords: this.keywords,
                title: this.product.name ?? this.product.translated?.name ?? this.parentProduct?.name ?? this.parentProduct?.translated?.name ?? '',
                languageId: this.apiContext.languageId,
            }

            try {
                const res = await this.textGenerationService.generate(productData);
                this.generatedDescription = res.data;

                this.createNotificationSuccess({
                    message: this.$tc('sw-product-generated-description.notifications.messageSuccess'),
                });
            } catch (_error) {
                this.createNotificationError({
                    message: this.$tc('sw-product-generated-description.notifications.messageError'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onCopy(): Promise<void> {
            if (!navigator?.clipboard) {
                // non-https polyfill
                domUtils.copyToClipboard(this.generatedDescription);
                this.createNotificationSuccess({
                    message: this.$tc('sw-product-generated-description.notifications.messageCopied'),
                });

                return;
            }

            try {
               await navigator.clipboard.writeText(this.generatedDescription);

               this.createNotificationSuccess({
                    message: this.$tc('sw-product-generated-description.notifications.messageCopied'),
                });
            } catch (error)  {
                let errorMessage = 'Unknown error';
                if (error?.response?.data?.errors?.length > 0) {
                    const errorDetailMsg = error.response.data.errors[0].detail;
                    errorMessage = `<br/> ${this.$tc('sw-product-generated-description.notifications.messageCopyFailed')}: "${errorDetailMsg}"`;
                }

                this.createNotificationError({
                    message: errorMessage,
                });
            }
        },

        onCancel(): void {
            this.$emit('modal-close');
        },
    }
});
