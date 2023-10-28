import type {PropType} from 'vue';
import template from './sw-flow-call-webhook-modal.html';
import './sw-flow-call-webhook-modal.scss';
import {Sequence, WebhookAction, FieldError, TriggerEvent, EntityProperty, Parameter} from '../../../../../type/types';
import {METHODS, BODIES} from "../../../../../constant/sw-flow-call-webhook.constant";

const { EntityDefinition, State } = Shopware;
const { ShopwareError } = Shopware.Classes;
const { isEmpty } = Shopware.Utils.types;
const { snakeCase } = Shopware.Utils.string;

/**
 * @package business-ops
 */
export default {
    template,

    props: {
        sequence: {
            type: Object as PropType<Sequence>,
            required: true,
            default: {}
        },
    },

    data(): {
        config: WebhookAction,
        error: FieldError,
        bodyType: string,
        methodOptions: Array<string>,
        bodyOptions: Array<string>,
        headers: [],
        query: [],
        formParams: [],
    } {
        return {
            config: {
                description: '',
                baseUrl: '',
                method: 'GET',
                authActive: false,
                options: {
                    auth: [],
                    query: {},
                    headers: {},
                    body: '',
                    form_params: {},
                },
            },
            error: {},
            bodyType: 'none',
            methodOptions: METHODS,
            bodyOptions: BODIES,
            headers: [],
            query: [],
            formParams: [],
        }
    },

    computed: {
        triggerEvent(): TriggerEvent {
            return State.get('swFlowState').triggerEvent;
        },

        dataSelection(): EntityProperty {
            return this.getEntityProperty(this.triggerEvent.data);
        },

        testUrl(): string {
            let query = '';

            this.query.forEach((param, index) => {
                if (!param.name || !param.data) {
                    return;
                }

                query += index === 0 ? '?' : '&';

                query += param.isCustomData
                    ? `${param.name}=${param.data}`
                    : `${param.name}={{${param.data}}}`;
            });

            return `${this.config.baseUrl}${query}`;
        },

        showTabBody(): boolean {
            return ['POST', 'PUT', 'PATCH'].includes(this.config.method);
        },
    },

    watch: {
        config: {
            handler(value: WebhookAction): void {
                if (value.baseUrl) {
                    this.$delete(this.error, 'baseUrl');
                }

                if (value.options.auth[0]) {
                    this.$delete(this.error, 'username');
                }

                if (value.options.auth[1]) {
                    this.$delete(this.error, 'password');
                }
            },
            deep: true,
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.config = this.sequence?.config ? { ...this.sequence?.config } : this.config;

            this.query = this.generateParams(this.config.options.query);
            this.headers = this.generateParams(this.config.options.headers);
            this.formParams = this.generateParams(this.config.options.form_params);

            if (this.config?.options?.body) {
                this.bodyType = 'raw';
            } else if (!isEmpty(this.config?.options?.form_params)) {
                this.bodyType = 'x-www-form-urlencoded';
            } else {
                this.bodyType = 'none';
            }
        },

        getEntityProperty(data: EntityProperty): EntityProperty {
            const entities = [];

            Object.keys(data).forEach(key => {
                if (data[key].type === 'entity') {
                    entities.push(key);
                }
            });

            return entities.reduce((result, entity) => {
                const entityName = this.convertCamelCaseToSnakeCase(entity);
                const properties = EntityDefinition.get(entityName).filterProperties(property => {
                    return EntityDefinition.getScalarTypes().includes(property.type);
                });

                return result.concat(Object.keys(properties).map(property => {
                    return {
                        value: `${entity}.${property}`,
                    };
                }));
            }, []);
        },

        onClose(): void {
            this.$emit('modal-close');
        },

        onAddAction(): void {
            this.checkError();

            if (!isEmpty(this.error)) {
                return;
            }

            if (this.bodyType === 'none' || this.config.method === 'GET') {
                this.$delete(this.config.options, 'form_params');
                this.$delete(this.config.options, 'body');
            } else if (this.bodyType === 'raw') {
                this.$delete(this.config.options, 'form_params');
            } else if (this.bodyType === 'x-www-form-urlencoded') {
                this.$delete(this.config.options, 'body');
                this.config.options.form_params = this.convertParams(this.formParams);
            }

            this.config.options.query = this.convertParams(this.query);
            this.config.options.headers = this.convertParams(this.headers);

            const sequence: Sequence = {
                ...this.sequence,
                config: this.config,
            };

            this.$emit('process-finish', sequence);
        },

        isExistData(item: WebhookAction): EntityProperty {
            return this.dataSelection.find(data => item === data.value);
        },

        generateParams: function (params: []): Array<Parameter> {
            if (isEmpty(params)) {
                return [{
                    name: '',
                    data: '',
                }];
            }

            const result: Array<Parameter> = Object.entries(params).map(([key, value]) => {
                //@ts-ignore
                const data = value.replace(/{{|}}/g, '');
                const isCustomData = !this.isExistData(data);

                return {
                    name: key,
                    data: isCustomData ? value : data,
                    isCustomData,
                };
            });

            return [...result, {data: '', name: ''}];
        },

        convertParams(data: Array<Parameter>): {} {
            const query = {};

            data.forEach(item => {
                if (!item.name) {
                    return;
                }

                if (item.isCustomData) {
                    query[item.name] = item.data;
                } else {
                    query[item.name] = item.data ? `{{${item.data}}}` : '';
                }
            });

            return query;
        },

        convertCamelCaseToSnakeCase(camelCaseText: string): string {
            return snakeCase(camelCaseText);
        },

        checkError(): void {
            const emptyError = new ShopwareError({
                code: 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
            });

            if (!this.config.baseUrl) {
                this.$set(this.error, 'baseUrl', emptyError);
            }

            if (this.config.authActive) {
                const { auth } = this.config.options;

                if (!auth[0] || !auth[1]) {
                    if (!auth[0]) {
                        this.$set(this.error, 'username', emptyError);
                    }

                    if (!auth[1]) {
                        this.$set(this.error, 'password', emptyError);
                    }
                }
            }
        },
    },
}
