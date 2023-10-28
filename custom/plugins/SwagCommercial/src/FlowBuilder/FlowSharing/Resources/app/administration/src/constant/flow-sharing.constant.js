/**
 * @package business-ops
 */
export const ACTION = Object.freeze({
    ADD_TAG: 'action.add.tag',
    ADD_ORDER_TAG: 'action.add.order.tag',
    ADD_CUSTOMER_TAG: 'action.add.customer.tag',
    REMOVE_TAG: 'action.remove.tag',
    REMOVE_ORDER_TAG: 'action.remove.order.tag',
    REMOVE_CUSTOMER_TAG: 'action.remove.customer.tag',
    SET_ORDER_STATE: 'action.set.order.state',
    GENERATE_DOCUMENT: 'action.generate.document',
    MAIL_SEND: 'action.mail.send',
    STOP_FLOW: 'action.stop.flow',
    SET_ORDER_CUSTOM_FIELD: 'action.set.order.custom.field',
    SET_CUSTOMER_CUSTOM_FIELD: 'action.set.customer.custom.field',
    SET_CUSTOMER_GROUP_CUSTOM_FIELD: 'action.set.customer.group.custom.field',
    CHANGE_CUSTOMER_GROUP: 'action.change.customer.group',
    CHANGE_CUSTOMER_STATUS: 'action.change.customer.status',
    ADD_CUSTOMER_AFFILIATE_AND_CAMPAIGN_CODE: 'action.add.customer.affiliate.and.campaign.code',
    ADD_ORDER_AFFILIATE_AND_CAMPAIGN_CODE: 'action.add.order.affiliate.and.campaign.code',
    APP_FLOW_ACTION: 'action.app.flow',
});

export const ACTION_ENTITY = Object.freeze({
    'action.add.order.tag': 'tag',
    'action.add.customer.tag': 'tag',
    'action.remove.order.tag': 'tag',
    'action.remove.customer.tag': 'tag',
    'action.change.customer.group': 'customer_group',
    'action.set.customer.custom.field': 'custom_field',
    'action.set.order.custom.field': 'custom_field',
    'action.set.customer.group.custom.field': 'custom_field',
    'action.mail.send': 'mail_template',
});

export const ACTION_GROUP_MISSING_ERROR = Object.freeze({
    'action.add.order.tag': 'tag',
    'action.add.customer.tag': 'customerTag',
    'action.remove.order.tag': 'orderTag',
    'action.remove.customer.tag': 'customerTag',
    'action.change.customer.group': 'customerGroup',
    'action.change.customer.status': 'customerStatus',
    'action.set.customer.custom.field': 'customerCustomField',
    'action.set.order.custom.field': 'orderCustomField',
    'action.set.customer.group.custom.field': 'customerGroupCustomField',
    'action.mail.send': 'emailTemplate',
});

export default {
    ACTION,
    ACTION_ENTITY,
    ACTION_GROUP_MISSING_ERROR,
};
