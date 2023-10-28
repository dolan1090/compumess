import type { PropType } from 'vue';
import template from './sw-settings-subscription-interval-advanced-settings-modal.html.twig';
import './sw-settings-subscription-interval-advanced-settings-modal.scss';
import type { ComponentHelper } from '../../../../type/types';
import type { IntervalState } from '../../../../state/interval.store';

const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['acl'],

    data(): {
        daysOfMonth: string[],
        monthsOfYear: string[],
        daysOfWeek: string[],
        frequency: number;
        unit: string;
        previewCronInterval: string;
        showPreviewBanner: boolean;
        multiSelectExpanded: boolean;
        } {
        return {
            daysOfMonth: [],
            monthsOfYear: [],
            daysOfWeek: [],
            frequency: 1,
            unit: 'M',
            previewCronInterval: '* * * * *',
            showPreviewBanner: false,
            multiSelectExpanded: false,
        };
    },

    props: {
        dateIntervalOptions: {
            required: true,
            type: Array as PropType<Array<{ label: string; value: string; }>>,
        },
    },

    computed: {
        ...mapState<IntervalState>('swSubscriptionInterval', [
            'interval',
            'dateInterval',
            'cronInterval',
        ]),

        daysOfMonthOptions(): { label: string, value: string }[] {
            return [...Array(31).keys()]
                .map((key) => ({
                    label: String(key + 1),
                    value: String(key + 1),
                }));
        },

        monthsOfYearOptions(): { label: string, value: string }[] {
            return [
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.january'), value: '1' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.february'), value: '2' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.march'), value: '3' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.april'), value: '4' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.may'), value: '5' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.june'), value: '6' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.july'), value: '7' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.august'), value: '8' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.september'), value: '9' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.october'), value: '10' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.november'), value: '11' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.monthsOfYear.december'), value: '12' },
            ];
        },

        daysOfWeekOptions(): { label: string; value: string }[] {
            return [
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.sunday'), value: '0' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.monday'), value: '1' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.tuesday'), value: '2' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.wednesday'), value: '3' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.thursday'), value: '4' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.friday'), value: '5' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.saturday'), value: '6' },
                { label: this.$tc('commercial.subscriptions.settings.interval.cronOptions.daysOfWeek.sunday'), value: '7' },
            ];
        },

        isDaysOfWeekDisabled(): boolean {
            return (this.daysOfMonth.length > 0 && this.daysOfWeek.length === 0) || !this.acl.can('plans_and_intervals.editor');
        },

        isDaysOfMonthDisabled(): boolean {
            return (this.daysOfWeek.length > 0 && this.daysOfMonth.length === 0) || !this.acl.can('plans_and_intervals.editor');
        },
    },

    mounted() {
        this.daysOfMonth = this.cronInterval.daysOfMonth;
        this.daysOfWeek = this.cronInterval.daysOfWeek;
        this.monthsOfYear = this.cronInterval.monthsOfYear;

        this.frequency = this.dateInterval.frequency;
        this.unit = this.dateInterval.unit;

        // prevent preview-banner to send multiple request because of initial value changes
        this.previewCronInterval = this.buildCronIntervalString();
        this.showPreviewBanner = true;
    },

    methods: {
        closeModal(): void {
            this.$emit('modal-close');
        },

        buildCronIntervalString(): string {
            const daysOfMonth = this.daysOfMonth.length === 0 ? '*' : this.daysOfMonth.join(',');
            const monthsOfYear = this.monthsOfYear.length === 0 ? '*' : this.monthsOfYear.join(',');
            const daysOfWeek = this.daysOfWeek.length === 0 ? '*' : this.daysOfWeek.join(',');

            return `* * ${daysOfMonth} ${monthsOfYear} ${daysOfWeek}`;
        },

        onSave(): void {
            // Persist local state in the interval (important for request later)
            const interval = this.interval;
            interval.cronInterval = this.buildCronIntervalString();
            interval.dateInterval = `P${this.frequency}${this.unit}`;
            Shopware.State.commit('swSubscriptionInterval/setInterval', interval);

            // Persist local state for cronInterval to fill the input fields again when modal is opened
            const cronInterval = {
                daysOfMonth: this.daysOfMonth,
                monthsOfYear: this.monthsOfYear,
                daysOfWeek: this.daysOfWeek,
            };
            Shopware.State.commit('swSubscriptionInterval/setCronInterval', cronInterval);

            // Persist local state for dateInterval to fill the input fields again when modal is opened
            const dateInterval = {
                frequency: this.frequency,
                unit: this.unit,
            };
            Shopware.State.commit('swSubscriptionInterval/setDateInterval', dateInterval);

            this.$emit('modal-close');
        },

        onMultiSelectExpanded(): void {
            this.multiSelectExpanded = true;
        },

        onMultiSelectCollapsed(): void {
            this.multiSelectExpanded = false;

            this.previewCronInterval = this.buildCronIntervalString();
        },

        onMultiSelectChanged(): void {
            if (this.multiSelectExpanded) return;

            this.previewCronInterval = this.buildCronIntervalString();
        },
    },
});
