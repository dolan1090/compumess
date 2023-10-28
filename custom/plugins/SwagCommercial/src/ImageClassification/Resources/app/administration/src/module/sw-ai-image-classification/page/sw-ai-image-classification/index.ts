import template from './sw-ai-image-classification.html.twig'
import './sw-ai-image-classification.scss'

export default Shopware.Component.wrapComponentConfig({
    template,

    inject: [ 'systemConfigApiService' ],

    data(): {
        useImageKeywordAssistantActive: boolean,
        keywordLanguageId: string,
        addKeywordsToImageAltText: boolean,
        altTextDataHandlingStrategy: 'overwrite' | 'append' | 'prepend' | 'keep',
        saving: boolean,
        isLoading: boolean,
    } {
        return {
            useImageKeywordAssistantActive: false,
            keywordLanguageId: Shopware.Context.api.systemLanguageId,
            addKeywordsToImageAltText: false,
            altTextDataHandlingStrategy: 'append',
            saving: false,
            isLoading: false,
        }
    },

    computed: {
        availableTextDataHandlingStrategies(): {
            value: 'overwrite' | 'append' | 'prepend' | 'keep', label: string
        }[] {
            return [
                { value: 'overwrite', label: this.$tc('sw-ai-image-classification.settings.handlingStrategies.overwrite') },
                { value: 'append', label: this.$tc('sw-ai-image-classification.settings.handlingStrategies.append') },
                { value: 'prepend', label: this.$tc('sw-ai-image-classification.settings.handlingStrategies.prepend') },
                { value: 'keep', label: this.$tc('sw-ai-image-classification.settings.handlingStrategies.keep') },
            ]
        }
    },

    beforeMount() {
        this.loadConfig();
    },

    methods: {
        loadConfig() {
            this.isLoading = true;

            this.systemConfigApiService.getValues('core.mediaAiTag', null).then((values) => {
                if (Shopware.Utils.object.hasOwnProperty(values, 'core.mediaAiTag.enabled')) {
                    this.useImageKeywordAssistantActive = values['core.mediaAiTag.enabled'];
                }

                if (Shopware.Utils.object.hasOwnProperty(values, 'core.mediaAiTag.targetLanguageId')) {
                    this.keywordLanguageId = values['core.mediaAiTag.targetLanguageId'];
                }

                if (Shopware.Utils.object.hasOwnProperty(values, 'core.mediaAiTag.addToAltText')) {
                    this.addKeywordsToImageAltText = values['core.mediaAiTag.addToAltText'];
                }

                if (Shopware.Utils.object.hasOwnProperty(values, 'core.mediaAiTag.altTextStrategy')) {
                    this.altTextDataHandlingStrategy = values['core.mediaAiTag.altTextStrategy'];
                }

                this.isLoading = false;
            })
        },

        onSave() {
            this.saving = true;
            this.isLoading = true;

            this.systemConfigApiService.saveValues(
                {
                    'core.mediaAiTag.enabled': this.useImageKeywordAssistantActive,
                    'core.mediaAiTag.targetLanguageId': this.keywordLanguageId,
                    'core.mediaAiTag.addToAltText': this.addKeywordsToImageAltText,
                    'core.mediaAiTag.altTextStrategy': this.altTextDataHandlingStrategy,
                }
            ).then(() => {
                this.isLoading = false;
                this.saving = false;
            })
        },
    }
})
