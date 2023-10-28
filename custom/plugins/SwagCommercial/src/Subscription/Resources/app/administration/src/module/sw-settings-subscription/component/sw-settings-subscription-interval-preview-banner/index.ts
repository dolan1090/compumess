import type { PropType } from 'vue';
import template from './sw-settings-subscription-interval-preview-banner.html.twig';
import './sw-settings-subscription-interval-preview-banner.scss';
import type { GeneratedIntervalPreview } from '../../../../type/types';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['subscriptionApiService'],

    props: {
        frequency: {
            required: true,
            type: Number,
        },
        frequencyUnit: {
            required: true,
            type: String as PropType<'D' | 'W' | 'M'>,
        },
        cronInterval: {
            required: true,
            type: String,
        },
    },

    data(): {
        showMoreModal: boolean,
        loading: boolean,
        timestamps: number[],
        } {
        return {
            showMoreModal: false,
            loading: true,
            timestamps: [],
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {
        dates(): (string | null)[] {
            return this.timestamps.map((timestamp) => this.formatDate(timestamp));
        },

        isError(): boolean {
            return this.timestamps.length === 0 || this.dates.includes(null);
        },
    },

    watch: {
        frequency(newFrequency, oldFrequency) {
            if (newFrequency === oldFrequency) return;
            this.loadPreview();
        },

        frequencyUnit(newUnit, oldUnit) {
            if (newUnit === oldUnit) return;
            this.loadPreview();
        },

        cronInterval(newInterval, oldInterval) {
            if (newInterval === oldInterval) return;
            this.loadPreview();
        },
    },

    methods: {
        createdComponent(): void {
            this.loadPreview();
        },

        loadPreview(): void {
            const cronInterval = this.cronInterval;
            const dateInterval = `P${this.frequency}${this.frequencyUnit}`;

            this.loading = true;

            this.subscriptionApiService
                .generateIntervalPreview(15, cronInterval, dateInterval)
                .then((response: GeneratedIntervalPreview) => this.timestamps = response.timestamps)
                .catch(() => this.timestamps = [])
                .finally(() => this.loading = false);
        },

        formatDate(timestamp: number): string | null {
            const date = new Date(timestamp * 1000);

            if (Number.isNaN(date.getDay())) return null;

            return Shopware.Utils.format.date(date.toDateString(), {
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: undefined,
                minute: undefined,
                skipTimezoneConversion: true,
            });
        },

        onOpenModal(): void {
            this.showMoreModal = true;
        },

        onCloseModal(): void {
            this.showMoreModal = false;
        },
    },
});
