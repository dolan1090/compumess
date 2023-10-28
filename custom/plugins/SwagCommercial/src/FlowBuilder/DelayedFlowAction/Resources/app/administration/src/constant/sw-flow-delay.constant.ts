export const enum SEQUENCE_TYPES {
    ACTION = 'action',
    CONDITION = 'condition',
    DELAY_ACTION = 'delay_action',
}

export const DELAY_OPTIONS = [
    {
        value: 'hour',
        label: 'sw-flow-delay.modal.labelHour'
    },
    {
        value: 'day',
        label: 'sw-flow-delay.modal.labelDay'
    },
    {
        value: 'week',
        label: 'sw-flow-delay.modal.labelWeek'
    },
    {
        value: 'month',
        label: 'sw-flow-delay.modal.labelMonth'
    },
    {
        value: 'custom',
        label: 'sw-flow-delay.modal.labelCustom'
    },
] as const;

export const CUSTOM_TIME = 'custom' as const;
export const GENERAL_GROUP = 'general' as const;
