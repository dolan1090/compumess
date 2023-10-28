import type { TEntity, DateInterval, CronInterval } from '../type/types';

export interface IntervalState {
    interval: TEntity<'subscription_interval'>,
    dateInterval: DateInterval;
    cronInterval: CronInterval
}

/**
 * @package checkout
 *
 * @public
 */
export default {
    namespaced: true,

    state: (): IntervalState => ({
        interval: {} as TEntity<'subscription_interval'>,
        dateInterval: { frequency: 1, unit: 'W' },
        cronInterval: {
            daysOfMonth: [],
            monthsOfYear: [],
            daysOfWeek: [],
        },
    }),

    mutations: {
        setInterval: (state: IntervalState, interval: TEntity<'subscription_interval'>): void => {
            state.interval = interval;
        },
        setDateInterval: (state: IntervalState, dateInterval: DateInterval): void => {
            state.dateInterval = dateInterval;
        },
        setCronInterval: (state: IntervalState, cronInterval: CronInterval): void => {
            state.cronInterval = cronInterval;
        },
    },

    actions: {},

    getters: {},
};
