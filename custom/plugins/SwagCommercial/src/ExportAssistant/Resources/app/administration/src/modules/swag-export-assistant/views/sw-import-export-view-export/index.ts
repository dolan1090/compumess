/**
 * @package system-settings
 */
import template from './sw-import-export-view-export.html.twig';
import './sw-import-export-view-export.scss';

export default {
    template,

    data(): {
        useExportAssistant: boolean,
        } {
        return {
            useExportAssistant: false,
        };
    },

    computed: {
        cardTitle(): string {
            return this.useExportAssistant
                ? this.$tc('sw-import-export.exporter.exportLabel')
                : this.$tc('swag-export-assistant.base.labelExportAssistant');
        },

        cardTitleClasses(): object {
            return {
                'sw-card__title': true,
                'sw-import-export-view-export__card-title': this.useExportAssistant,
            };
        },

        cardTitleContent(): string {
            return this.useExportAssistant
                ? this.$tc('swag-export-assistant.base.labelExportAssistant')
                : this.$tc('sw-import-export.exporter.exportLabel');
        },

        cardSubtitleContent(): string {
            return this.useExportAssistant
                ? this.$tc('swag-export-assistant.base.labelExportDefault')
                : this.$tc('swag-export-assistant.base.labelExportAssistant');
        },
    },

    methods: {
        toggleUseExportAssistant(): void {
            this.useExportAssistant = !this.useExportAssistant;
        },
    },
};
