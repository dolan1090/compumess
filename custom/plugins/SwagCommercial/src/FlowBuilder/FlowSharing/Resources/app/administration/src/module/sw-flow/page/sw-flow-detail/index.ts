import type Repository from 'src/core/data/repository.data';
import type {
    DataIncluded,
    EntityData,
    Error,
    Flow,
    ReferenceIncluded,
    Rule,
    Condition,
    Sequence,
} from '../../flow.types';
import {ACTION_ENTITY} from '../../../../constant/flow-sharing.constant';

interface RuleReference {
    [key: string]: Array<string>
}

const { State, Utils } = Shopware;
const { Criteria } = Shopware.Data;
const { cloneDeep } = Utils.object;

/**
 * @package business-ops
 */
export default {
    data(): {
        dataIncluded: DataIncluded,
        referenceIncluded: ReferenceIncluded,
        invalidEntityIds: Array<string>
    } {
        return {
            dataIncluded: {},
            referenceIncluded: {},
            invalidEntityIds: []
        };
    },

    computed: {
        flowRepository(): Repository {
            return this.repositoryFactory.create('flow');
        },

        flowSequenceRepository(): Repository {
            return this.repositoryFactory.create('flow_sequence');
        },

        flowSharingState() {
            return State.get('swFlowSharingState');
        },

        isUploading() {
            return this.$route.params?.isUploading === true;
        }
    },

    methods: {
        async createdComponent(): Promise<void> {
            if (this.isUploading) {
                this.referenceIncluded = this.flowSharingState.referenceIncluded;
                this.dataIncluded = this.flowSharingState.dataIncluded;

                await this.getInvalidEntityIds();
            }

            this.$super('createdComponent');
        },

        routeDetailTab(tabName: string): {
            name: string,
            params: {
                [key: string]: boolean | string
            }
        } {
            if (!this.isUploading) {
                return this.$super('routeDetailTab', tabName);
            }

            return { name: `sw.flow.create.${tabName}`, params: { isUploading: true } };
        },

        createNewFlow(): void {
            if (!this.isUploading) {
                return this.$super('createNewFlow');
            }

            const flow = this.flowRepository.create();
            flow.priority = 0;
            flow.eventName = '';

            State.commit('swFlowState/setFlow', flow);

            const flowObject = this.flowSharingState.flow;

            if (flowObject) {
                this.restoreImportedFlow(flowObject);
            }
        },

        restoreImportedFlow(flowObject: Flow): void {
            if (Object.keys(flowObject).length === 0) {
                return;
            }
            this.flow.id = Utils.createId();

            Object.keys(flowObject).forEach((key) => {
                if (['id', 'sequences'].includes(key)) {
                    return;
                }

                this.flow[key] = key === 'active'
                    ? Boolean(flowObject[key])
                    : flowObject[key];
            });

            let sequences = flowObject?.sequences;
            sequences = sequences ? this.buildSequencesFromConfig(this.validateSequences(sequences)) : [];

            State.commit('swFlowState/setFlow', this.flow);
            State.commit('swFlowState/setEventName', flowObject?.eventName);

            State.commit('error/removeApiError', {
                expression: `flow.${this.flow.id}.eventName`,
            });

            State.commit('swFlowState/setSequences', sequences);
            State.commit('swFlowState/setOriginFlow', cloneDeep(this.flow));
            this.getDataForActionDescription();
        },

        validateSequences(sequences: Array<Sequence>): Array<Sequence> {
            if (this.dataIncluded['rule']) {
                sequences = this.addRuleErrors(sequences);
            }

            sequences = this.addActionErrors(sequences);

            return sequences;
        },

        addRuleErrors(sequences: Array<Sequence>): Array<Sequence> {
            Object.keys(sequences).forEach((key) => {
                if (sequences[key].actionName !== null) {
                    return;
                }

                sequences[key].rule = this.dataIncluded.rule[sequences[key].ruleId];
                sequences[key].rule.value = Object.assign({}, sequences[key].rule.value);

                if (this.invalidEntityIds.includes(sequences[key].ruleId)) {
                    sequences[key].error = this.generateErrorObject(
                        'missing-rule',
                        'rule',
                        [sequences[key].ruleId]
                    );

                    return;
                }

                if (this.hasInvalidReference(sequences[key].rule)) {
                    sequences[key].error = this.generateErrorObject(
                        'rule',
                        'rule',
                        [sequences[key].ruleId]
                    );
                }
            });

            return sequences;
        },

        addActionErrors(sequences: Array<Sequence>): Array<Sequence> {
            Object.keys(sequences).forEach((key) => {
                if (sequences[key].actionName === null) {
                    return;
                }

                if (ACTION_ENTITY[sequences[key].actionName] === undefined) {
                    return;
                }

                const entity = ACTION_ENTITY[sequences[key].actionName];
                const invalidIds = this.getEntityIds(entity, sequences[key].config).filter((id) => {
                    return this.invalidEntityIds.includes(id);
                });

                if (invalidIds.length === 0) {
                    return;
                }
                sequences[key].error = this.generateErrorObject('action', entity, invalidIds);
            });

            return sequences;
        },

        getEntityIds(entity: string, config): Array<string> {
            switch (entity) {
                case 'tag':
                    return Object.keys(config.tagIds);
                case 'customer_group':
                    return [config.customerGroupId];
                case 'custom_field':
                    return [config.customFieldId];
                case 'mail_template':
                    return [config.mailTemplateId];
                default:
                    return [];
            }
        },

        async getInvalidEntityIds(): Promise<void> {
            const entities = Object.assign({}, this.referenceIncluded, this.dataIncluded);
            for (const entity of Object.keys(entities)) {
                const repository = this.repositoryFactory.create(entity);

                const criteria = new Criteria(1, null);
                criteria.addFilter(Criteria.equalsAny('id', Object.keys(entities[entity])));

                const existingIds = await repository.searchIds(criteria);

                const invalidIds = Object.keys(entities[entity]).filter((id) => {
                    return !existingIds.data.includes(id);
                });

                this.invalidEntityIds.push(...invalidIds);
            }

            const ruleReferences = this.getReferenceIncludedFromRule();
            for (const entity of Object.keys(ruleReferences)) {
                const repository = this.repositoryFactory.create(entity);

                const criteria = new Criteria(1, null);
                criteria.addFilter(Criteria.equalsAny('id', ruleReferences[entity]));

                const existingIds = await repository.searchIds(criteria);

                const invalidIds = ruleReferences[entity].filter((id) => {
                    return !existingIds.data.includes(id);
                });

                this.invalidEntityIds.push(...invalidIds);
            }
        },

        generateErrorObject(
            type: string,
            entity: string,
            invalidIds: Array<string>
        ): Error {
            let error = Object.assign({}, {
                type: type,
                errorDetail: {}
            });

            const entities = Object.assign({}, this.referenceIncluded, this.dataIncluded);

            invalidIds.forEach((id) => {
                if (!Array.isArray(entities[entity][id])) {
                    error.errorDetail = {
                        [`${entity}`]: {
                            [`${id}`]: entities[entity][id]
                        }
                    };

                    return;
                }

                const entityReference = (entities[entity][id] as Array<EntityData>).filter((item) => {
                    return item.locale === State.get('session').currentLocale;
                });

                if (entityReference.length > 0) {
                    error.errorDetail = {
                        [`${entity}`]: {
                            [`${id}`]: entityReference[0]
                        }
                    };
                }
            });

            return error;
        },

        hasInvalidReference(rule: Rule): boolean {
            const isValid = rule.conditions.every((condition) => {
                const entityIds = this.getEntityIdsOfCondition(condition);

                if (entityIds.length === 0) {
                    return true;
                }

                const invalidIds = entityIds.filter((id) => {
                    return this.invalidEntityIds.includes(id);
                });

                return invalidIds.length === 0;
            });

            return !isValid;
        },

        getEntityIdsOfCondition(condition: Condition): Array<string> {
            const config = State.getters['ruleConditionsConfig/getConfigForType'](condition.type);

            if (!config || !config.fields.length) {
                return [];
            }

            if (!Object.keys(config.fields[0].config).includes('entity')) {
                return [];
            }

            return config.fields[0].type === 'multi-entity-id-select'
                ? condition.value[config.fields[0].name]
                : [condition.value[config.fields[0].name]];
        },

        getReferenceIncludedFromRule(): RuleReference {
            const ruleReferences = {};

            if (!this.dataIncluded.rule) {
                return ruleReferences;
            }

            Object.keys(this.dataIncluded.rule).forEach((ruleId) => {
                this.dataIncluded.rule[ruleId].conditions.forEach((condition) => {
                    const config = State.getters['ruleConditionsConfig/getConfigForType'](condition.type);

                    const entityIds = this.getEntityIdsOfCondition(condition);

                    if (entityIds.length > 0) {
                        const entityName = config.fields[0].config.entity;
                        if (!ruleReferences[entityName]) {
                            ruleReferences[entityName] = entityIds;
                        } else {
                            ruleReferences[entityName].push(...entityIds);
                        }
                    }
                });
            });

            return ruleReferences;
        },

        onSave(): void {
            if (this.isUploading) {
                const errorSequences = this.sequences.filter((sequence) => sequence.error && Object.keys(sequence.error).length);

                if (errorSequences.length) {
                    this.createNotificationError({
                        message: this.$tc('sw-flow.flowNotification.messageSaveError'),
                    });

                    return null;
                }
            }

            this.$super('onSave');
        },
    },
};
