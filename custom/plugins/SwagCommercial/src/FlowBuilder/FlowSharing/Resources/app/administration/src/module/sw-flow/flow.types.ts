import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

interface Action {
    name: string,
    config: {},
}

interface MailTemplateEntity extends Entity {
    id: string,
    mailTemplateTypeId: string
}

interface MailTemplate {
    id: string,
    locale: string,
    technicalName: string,
    senderName: string,
    subject: string,
    description: string,
    contentHtml: string,
    contentPlain: string,
    customFields: {},
    mailTemplateTypeId: string,
    mailTemplateTypeName: string,
}

interface DataIncluded {
    rule?: {
        [key: string]: Rule
    },
    mail_template?: {
        [key: string]: Array<MailTemplate>
    }
}

interface EntityData {
    id: string,
    name?: string,
    locale?: string
}

interface ReferenceIncluded {
    [key: string]: {
        [key: string]: EntityData | Array<EntityData>
    },
}

interface RuleEntity extends Entity {
    id: string,
    name: string
}

interface Rule {
    id: string,
    name: string,
    conditions: Array<Condition>,
    _isNew?: boolean,
}

interface Condition {
    id: string,
    ruleId: string,
    parentId: string,
    type: string,
    value: {}
}

interface Requirement {
    shopwareVersion?: string,
    pluginInstalled?: Array<string>,
    appInstalled?: Array<string>
}

interface SequenceEntity extends Entity {
    id: string,
    actionName: string,
    flowId: string
}

interface Sequence {
    id: string,
    parentId: string,
    ruleId: string,
    actionName: string
}

interface FlowEntity extends Entity {
    id: string,
    name: string,
    description: string,
    eventName: string,
}

interface Flow {
    id: string,
    name: string,
    eventName: string,
    sequences: Array<Sequence>
};

interface FlowData {
    flow: Flow,
    dataIncluded: DataIncluded,
    requirements: Requirement
}

interface Error {
    type: string,
    errorDetail: {
        [key: string]: {
            [key: string]: EntityData
        }
    }
}

interface ErrorData {
    [key: string]: {
        id: string,
        name?: string,
        mailTemplateTypeName?: string
    }
}

interface RuleConflictOption {
    value: boolean,
    name: string,
    description: string,
}

/**
 * @private
 * @package business-ops
 */
export type {
    Action,
    EntityData,
    Error,
    ErrorData,
    Flow,
    FlowData,
    FlowEntity,
    DataIncluded,
    MailTemplate,
    MailTemplateEntity,
    ReferenceIncluded,
    Requirement,
    Rule,
    Condition,
    RuleEntity,
    Sequence,
    SequenceEntity,
    RuleConflictOption,
};
