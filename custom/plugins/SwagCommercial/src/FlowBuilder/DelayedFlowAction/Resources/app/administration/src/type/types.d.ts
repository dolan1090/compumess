export interface Sequence {
    id: string,
    actionName: string,
    apiAlias: string,
    appFlowActionId: string,
    children: [],
    config: {
        delay: DelayConfig[],
    },
    parentId: string,
    position: number,
    ruleId: string,
    trueCase: boolean,
    displayGroup: number,
    customFields: string,
    name?: string,
    rule: {
        name: string
    }
    sequence?: {
        children: [
            {
                id: string,
                actionName: string,
                apiAlias: string,
                appFlowActionId: string,
                children: [],
                config: {
                    delay: DelayConfig[],
                },
                parentId: string,
                position: number,
                name?: string,
                ruleId: string,
                trueCase: boolean,
                displayGroup: number,
                customFields: string,
                rule: {
                    name: string
                }
            }
        ],
    }
}

export interface Rule {
    name: string,
    id: string
}

export type DelayType = 'hour' | 'day' | 'week' | 'month' | 'custom';
export type DelayAction = {
    value: string,
    label: string,
}

export type ActionConfig = {
    entity: string,
    tagIds: [],
    mailTemplateId: string,
    order: string,
    order_delivery: string,
    order_transaction: string,
    force_transition: string,
    documentTypes: [],
    documentType: string,
    customerGroupId: string,
    active: boolean,
    customFieldSetId: string,
    customFieldId: string,
    optionLabel: string,
    affiliateCode: {
        upsert: string,
        value: string,
    },
    campaignCode: {
        upsert: string,
        value: string,
    }
}

export type documentTypes = {
    documentType: string
}

export type DelayConfig = {
    type: string,
    value: string,
}

export type ActionOption = {
    label?: string,
    icon?: string,
    iconRaw?: string,
    name?: string,
    value?: string,
    disabled?: boolean,
    group?: string,
    translated?: {
        label: string
    }
}

export type AlertType = 'info' | 'warning';

export type NotificationType = {
    text: string,
    type: AlertType
}

export type SortType = 'ASC' | 'DESC';

export type DelayOption = {
    label: string,
    value: string,
}

export type FieldError = {
    _code: string,
    _detail: string,
    _id: string,
    _status: string,
}

export type WarningConfig = {
    enabled: boolean,
    type: string,
    id: string,
    name: string,
    actionType: string,
    rule?: string,
    clickableOption: {
        target: string,
        key: number,
    },
}

export type DelayActionColumn = {
    property: string,
    dataIndex: string,
    label: string,
    allowResize?: boolean,
}
