/*
 * @package checkout
 */

import template from './sw-system-config.html.twig';
import './sw-system-config.scss';

Shopware.Component.override('sw-system-config', {
    template,

    computed: {
        currentCharacterLimit(): number|null {
            return this.actualConfigData[this.currentSalesChannelId]['core.cart.aiCheckoutMessageCharcaterLimit']
                || null;
        },
        currentKeywords(): string[] {
            return this.actualConfigData[this.currentSalesChannelId]['core.cart.aiCheckoutMessageKeywords']
                || [];
        },
    },

    methods: {
        async createdComponent() {
            this.isLoading = true;

            await this.$super('createdComponent');

            if (!Shopware.License.get('CHECKOUT_SWEETENER-0270128')) {
                return;
            }

            if (this.domain !== 'core.cart') {
                return;
            }

            this.addCheckoutMessageCard();
        },

        addCheckoutMessageCard() {
            this.config.splice(-1, 0, {
                elements: [
                    {
                        config: {
                            componentName: 'sw-settings-cart-ai-card-description',
                        }
                    },
                    {
                        config: {
                            label: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.activateAiCopilot', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.activateAiCopilot', 0, 'en-GB'),
                            },
                        },
                        type: 'bool',
                        name: 'core.cart.aiCheckoutMessageActive',
                    },
                    {
                        config: {
                            label: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.toneOfVoice', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.toneOfVoice', 0, 'en-GB'),
                            },
                            helpText: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.toneOfVoiceHelpText', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.toneOfVoiceHelpText', 0, 'en-GB'),
                            },
                            options: [
                                {
                                    id: 'neutral',
                                    name: {
                                        'de-DE': this.$tc('commercial.checkout-sweetener.keywordOptions.neutral', 0, 'de-DE'),
                                        'en-GB': this.$tc('commercial.checkout-sweetener.keywordOptions.neutral', 0, 'en-GB'),
                                    },
                                },
                                {
                                    id: 'exciting',
                                    name: {
                                        'de-DE': this.$tc('commercial.checkout-sweetener.keywordOptions.exciting', 0, 'de-DE'),
                                        'en-GB': this.$tc('commercial.checkout-sweetener.keywordOptions.exciting', 0, 'en-GB'),
                                    },
                                },
                                {
                                    id: 'humorous',
                                    name: {
                                        'de-DE': this.$tc('commercial.checkout-sweetener.keywordOptions.humorous', 0, 'de-DE'),
                                        'en-GB': this.$tc('commercial.checkout-sweetener.keywordOptions.humorous', 0, 'en-GB'),
                                    },
                                },
                            ],
                        },
                        type: 'multi-select',
                        name: 'core.cart.aiCheckoutMessageKeywords',
                    },
                    {
                        config: {
                            label: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.characterLimit', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.characterLimit', 0, 'en-GB'),
                            },
                            helpText: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.characterLimitHelpText', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.characterLimitHelpText', 0, 'en-GB'),
                            },
                            max: 600,
                            allowEmpty: true,
                        },
                        type: 'int',
                        name: 'core.cart.aiCheckoutMessageCharcaterLimit',
                    },
                    {
                        config: {
                            componentName: 'sw-entity-multi-id-select',
                            entity: 'rule',
                            label: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.availabilityRule', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.availabilityRule', 0, 'en-GB'),
                            },
                            helpText: {
                                'de-DE': this.$tc('commercial.checkout-sweetener.availabilityRuleHelpText', 0, 'de-DE'),
                                'en-GB': this.$tc('commercial.checkout-sweetener.availabilityRuleHelpText', 0, 'en-GB'),
                            },
                        },
                        name: 'core.cart.aiCheckoutMessageAvailabilityRules',
                    },
                    {
                        config: {
                            componentName: 'sw-settings-cart-ai-card-preview-link',
                        },
                        name: 'core.cart.aiCheckoutMessagePreview',
                    },
                ],
                name: null,
                title: {
                    'de-DE': this.$tc('commercial.checkout-sweetener.title', 0, 'de-DE'),
                    'en-GB': this.$tc('commercial.checkout-sweetener.title', 0, 'en-GB'),
                },
                aiBadge: true
            })
        },
    }
});
