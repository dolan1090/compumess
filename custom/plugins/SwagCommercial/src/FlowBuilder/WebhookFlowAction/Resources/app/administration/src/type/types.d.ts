export interface Sequence {
    id: string,
    actionName: string,
    apiAlias: string,
    appFlowActionId: string,
    children: [],
    config: {},
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

export type ActionDescription = {
    'action.call.webhook': Object
}

export type WebhookAction = {
    method: string,
    baseUrl: string,
    description: string,
    authActive: boolean,
    options: {
        auth: Array<string>,
        query: Object,
        headers: Object,
        body: string,
        form_params: Object,
    }
}

export type FieldError = {
    _code?: string,
    _detail?: string,
    _id?: string,
    _status?: string,
}

export type ParameterColumn = {
    label: string,
    property: string,
    dataIndex: string,
    primary: boolean,
    width: string,
}

export type Parameter = {
    data: string,
    name: string,
    isCustomData?: boolean,
}

export type TriggerEvent = {
    aware: [],
    name: string,
    class: string,
    extension: [],
}

export type EntityProperty = {
    entityClass: string,
    type: string,
}

export type LogColumn = {
    primary?: boolean,
    property: string,
    dataIndex: string,
    label: string,
    allowResize?: boolean,
}

export type StatusOption = {
    id: string,
    name: string
}

export type Properties = {
    isLoading: boolean,
    toDate: string,
    fromDate: string,
    selectedStatus: string
}
