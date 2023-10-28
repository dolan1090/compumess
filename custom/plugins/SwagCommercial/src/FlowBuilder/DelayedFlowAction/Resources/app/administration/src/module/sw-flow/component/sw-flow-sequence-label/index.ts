import type {PropType} from 'vue';
import template from './sw-flow-sequence-label.html';
import './sw-flow-sequence-label.scss';
import type {Entity} from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import {ActionOption} from "../../../../type/types";

const { Component } = Shopware;

/**
 * @package business-ops
 */
export default Component.wrapComponentConfig({
    template,

    inject: ['flowBuilderService'],

    props: {
        sequence: {
            type: Object as PropType<Entity<'flow_sequence'>>,
            default: {}
        },

        appFlowActions: {
            type: Array as PropType<ActionOption>,
            default: [],
        },

        classes: {
            type: String,
            default: ''
        },
    },

    methods: {
        convertSequence(sequence: Entity<'flow_sequence'>): ActionOption {
            if (sequence.rule?.name){
                return {
                    label: sequence.rule?.name,
                    icon: 'regular-rule-s',
                }
            }

            const appFlowAction = Object.values(this.appFlowActions).find(item => item.name === sequence.actionName);
            if (appFlowAction) {
                return {
                    label: appFlowAction.translated?.label || appFlowAction.label,
                    icon: appFlowAction.iconRaw || appFlowAction.swIcon,
                    iconRaw: appFlowAction.icon,
                }
            }

            // for core actions
            return {
                label: `${this.$tc(this.flowBuilderService.getActionTitle(sequence.actionName)?.label)}`,
                icon: this.flowBuilderService.getActionTitle(sequence.actionName)?.icon,
            }
        },
    },
});
