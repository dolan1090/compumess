import template from './sw-settings-subscription-interval-detail.html.twig';
import './sw-settings-subscription-interval-detail.scss';
import type { ComponentHelper, DurationObject, DateInterval, CronInterval } from '../../../../type/types';
import type Repository from 'src/core/data/repository.data';
import swSubscriptionState from '../../../../state';
import type { IntervalState } from '../../../../state/interval.store';

const { Criteria } = Shopware.Data;
const { warn } = Shopware.Utils.debug;
const { mapState, mapPropertyErrors } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Shopware.Mixin.getByName('placeholder'),
    ],

    data(): {
        isIntervalLoading: boolean;
        isProcessLoading: boolean;
        isSaveSuccessful: boolean;
        isAdvancedSettingsModalOpen: boolean;
        hasSubscriptions: boolean;
        } {
        return {
            isIntervalLoading: false,
            isProcessLoading: false,
            isSaveSuccessful: false,
            isAdvancedSettingsModalOpen: false,
            hasSubscriptions: false,
        };
    },

    computed: {
        ...mapPropertyErrors('interval', [
            'name',
        ]),

        ...mapState<IntervalState>('swSubscriptionInterval', [
            'interval',
            'dateInterval',
            'cronInterval',
        ]),

        repository(): Repository<'subscription_interval'> {
            return this.repositoryFactory.create('subscription_interval');
        },

        dateIntervalOptions(): { label: string; value: string; }[] {
            return [
                {
                    label: this.$tc('commercial.subscriptions.settings.interval.frequencyDays'),
                    value: 'D',
                },
                {
                    label: this.$tc('commercial.subscriptions.settings.interval.frequencyWeeks'),
                    value: 'W',
                },
                {
                    label: this.$tc('commercial.subscriptions.settings.interval.frequencyMonths'),
                    value: 'M',
                },
            ];
        },

        hasAdvancedSettings(): boolean {
            return Object
                .keys(this.cronInterval)
                .reduce((acc, key) => acc || this.cronInterval[key].length > 0, false);
        },
    },

    beforeCreate(): void {
        Shopware.State.registerModule('swSubscriptionInterval', swSubscriptionState.modules.interval);
    },

    created(): void {
        this.createdComponent();
    },

    beforeDestroy(): void {
        Shopware.State.unregisterModule('swSubscriptionInterval');
    },

    methods: {
        parseDateIntervalString(dateIntervalString: string): DurationObject {
            // Omit 'weeks' here because dateIntervalString API Response doesn't contain it by default
            // Has to be calculated in the return object
            const [, years, months, days] = dateIntervalString.match(/P(\d+Y)?(\d+M)?(\d+D)?/) ?? [];

            const yearsValue = parseInt(years?.slice(0, -1) || '0');
            const monthsValue = parseInt(months?.slice(0, -1) || '0');
            const daysValue = parseInt(days?.slice(0, -1) || '0');

            return {
                M: monthsValue + yearsValue * 12,
                W: daysValue % 7 === 0 ? daysValue / 7 : 0,
                D: daysValue % 7 > 0 ? daysValue : 0,
            };
        },

        evaluateDateInterval(durationObject: DurationObject): DateInterval {
            for (const [unit, frequency] of Object.entries(durationObject)) {
                if (frequency > 0) {
                    return { frequency, unit };
                }
            }

            return { frequency: 0, unit: '' };
        },

        extractDateInterval(dateIntervalString: string): DateInterval {
            const dateIntervalAsObject: DurationObject = this.parseDateIntervalString(dateIntervalString);
            return this.evaluateDateInterval(dateIntervalAsObject);
        },

        extractCronInterval(cronIntervalString: string): CronInterval {
            // Omit first two parts "minutes" and "hours" because they are not significant
            const [, , daysOfMonth, monthsOfYear, daysOfWeek] = cronIntervalString.split(' ');

            return {
                daysOfMonth: daysOfMonth === '*' ? [] : daysOfMonth.split(','),
                monthsOfYear: monthsOfYear === '*' ? [] : monthsOfYear.split(','),
                daysOfWeek: daysOfWeek === '*' ? [] : daysOfWeek.split(','),
            };
        },

        createdComponent(): void {
            if (this.$route.params.id) {
                void this.loadIntervalById(this.$route.params.id);

                return;
            }

            this.createNewInterval();

            if (!Shopware.State.getters['context/isSystemDefaultLanguage']) {
                Shopware.State.commit('context/resetLanguageToDefault');
            }
        },

        createNewInterval(): void {
            const newInterval = this.repository.create();
            newInterval.active = false;
            newInterval.dateInterval = 'P7D';
            newInterval.cronInterval = '* * * * *';

            Shopware.State.commit('swSubscriptionInterval/setDateInterval', this.extractDateInterval(newInterval.dateInterval));
            Shopware.State.commit('swSubscriptionInterval/setCronInterval', this.extractCronInterval(newInterval.cronInterval));
            Shopware.State.commit('swSubscriptionInterval/setInterval', newInterval);
        },

        async loadIntervalById(id: string): Promise<void> {
            this.isIntervalLoading = true;

            const criteria = new Criteria(1, 25);
            criteria.getAssociation('subscriptions').setLimit(1);

            const context = { ...Shopware.Context.api, inheritance: true };

            const interval = await this.repository.get(id, context, criteria);
            this.hasSubscriptions = interval.subscriptions.length > 0;

            Shopware.State.commit('swSubscriptionInterval/setDateInterval', this.extractDateInterval(interval.dateInterval));
            Shopware.State.commit('swSubscriptionInterval/setCronInterval', this.extractCronInterval(interval.cronInterval));
            Shopware.State.commit('swSubscriptionInterval/setInterval', interval);

            this.isIntervalLoading = false;
        },

        onCancel(): void {
            void this.$router.push({ name: 'sw.settings.subscription.index.intervals' });
        },

        async onSave(): Promise<void> {
            this.isProcessLoading = true;
            this.isSaveSuccessful = false;

            try {
                this.interval.dateInterval = `P${this.dateInterval.frequency}${this.dateInterval.unit}`;

                await this.repository.save(this.interval);

                this.isSaveSuccessful = true;

                if (!this.$route.params.id) {
                    await this.$router.replace({
                        name: 'sw.settings.subscription.intervalDetail',
                        params: { id: this.interval.id },
                    });
                }

                await this.loadIntervalById(this.$route.params.id);
            } catch (error: any) {
                warn(this._name, error.message, error.response);
                throw error;
            } finally {
                this.isProcessLoading = false;
            }
        },

        onSaveRule(ruleId: string): void {
            this.interval.availabilityRuleId = ruleId;
        },

        toggleAdvancedSettingsModal(): void {
            this.isAdvancedSettingsModalOpen = !this.isAdvancedSettingsModalOpen;
        },

        onChangeLanguage(languageId: string): void {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.createdComponent();
        },
    },
});
