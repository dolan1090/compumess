import type {PropType} from 'vue';
import template from './sw-flow-call-webhook-parameter-grid.html';
import {ParameterColumn, Parameter} from '../../../../type/types';

/**
 * @package business-ops
 */
export default {
    template,

    model: {
        prop: 'parameters',
        event: 'change',
    },

    props: {
        parameters: {
            type: Array as PropType<Parameter>,
            default: {},
            required: true,
        },

        dataSelection: {
            type: Array as PropType<{}>,
            default: [],
            required: true,
        },
    },

    data():{
        records: Array<Parameter>,
    } {
        return {
            records: this.parameters,
        }
    },

    computed: {
        parameterColumns(): Array<ParameterColumn> {
            return [
                {
                    label: this.$tc('sw-flow-call-webhook.modal.columnName'),
                    property: 'name',
                    dataIndex: 'name',
                    primary: true,
                    width: '250px',
                },
                {
                    label: this.$tc('sw-flow-call-webhook.modal.columnData'),
                    property: 'data',
                    dataIndex: 'data',
                    primary: true,
                    width: '250px',
                },
            ];
        },
    },

    watch: {
        parameters: {
            handler(value: []): void {
                if (!value || !value.length) {
                    return;
                }

                this.records = value;
            },
        },
    },

    methods: {
        changeToCustomText(item: Parameter, itemIndex: number): void {
            this.$set(this.records, itemIndex, { ...item, isCustomData: !item.isCustomData });
            this.$emit('change', this.records);
        },

        onChangeItem(item: Parameter, itemIndex: number): void {
            if (!item.name || !item.data || itemIndex !== this.records.length - 1) {
                return;
            }

            this.records = [...this.records, { name: '', data: '' }];
            this.$emit('change', this.records);
        },

        deleteItem(itemIndex: number) {
            this.$delete(this.records, itemIndex);
            this.$emit('change', this.records);
        },

        disableDelete(itemIndex: number): boolean {
            return itemIndex === this.records.length - 1;
        },
    },
}
