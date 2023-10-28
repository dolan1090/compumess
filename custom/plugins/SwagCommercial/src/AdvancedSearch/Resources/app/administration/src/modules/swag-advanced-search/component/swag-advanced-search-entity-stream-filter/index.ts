/**
 * @package buyers-experience
 */
import template from './swag-advanced-search-entity-stream-filter.html.twig';
import './swag-advanced-search-entity-stream-filter.scss';

const { EntityDefinition } = Shopware;

export default {
    template,

    inject: [
        'createCondition',
        'insertNodeIntoTree',
        'removeNodeFromTree',
        'entityStream',
    ],

    computed: {
        actualCondition() {
            if (this.condition.type === 'not') {
                return this.condition.queries[0];
            }

            return this.condition;
        },

        fields: {
            get() {
                if (!this.actualCondition.field) {
                    return [];
                }

                return this.actualCondition.field.split('.');
            },
            set(fields) {
                let concatenation = fields.join('.');

                if (concatenation.match('\.$')) {
                    concatenation = concatenation.substr(0, concatenation.length);
                }

                if (!concatenation) {
                    this.actualCondition.field = null;
                    return;
                }

                this.actualCondition.field = concatenation;
            },
        },

        fieldDefinitions() {
            let currentDefinition = EntityDefinition.get(this.entityStream.type);

            const fieldDefinitions = [currentDefinition];
            this.fields.forEach((fieldName) => {
                const fieldDefinition = currentDefinition.getField(fieldName);

                if (!fieldDefinition) {
                    return;
                }

                if (fieldDefinition.type === 'association') {
                    currentDefinition = EntityDefinition.get(fieldDefinition.entity);
                    fieldDefinitions.push(currentDefinition);
                }
            });

            return fieldDefinitions;
        },

        lastField() {
            if (this.fieldDefinitions.length > this.fields.length) {
                return {
                    fieldName: null,
                    definition: EntityDefinition.get('product'),
                };
            }

            const fieldName = this.fields[this.fields.length - 1];
            const definition = this.fieldDefinitions[this.fieldDefinitions.length - 1];

            return {
                fieldName,
                definition,
            };
        },
    },

    methods: {
        updateFields({ field, index }) {
            const fields = this.fields.slice(0, index);

            if (field) {
                fields.push(field);
            }

            this.changeType({ type: null, parameters: null });

            this.fields = fields;
        },

        changeType({ type, parameters }) {
            if (type === null) {
                if (this.condition.type === 'not') {
                    this.unwrapNot(this.condition, null);
                }
            }

            if (this.conditionDataProviderService.isNegatedType(type) &&
                this.condition.type !== 'not'
            ) {
                this.wrapInNot(this.condition, type, parameters);
                return;
            }

            if (this.condition.type === 'not' &&
                !this.conditionDataProviderService.isNegatedType(type)
            ) {
                this.unwrapNot(this.condition, type, parameters);
                return;
            }

            // Negation type stays the same
            this.actualCondition.type = type;
            this.actualCondition.value = null;
            this.actualCondition.parameters = parameters;
        },

        wrapInNot(condition, newType, parameters) {
            const { identifier: negatedType } = this.conditionDataProviderService.negateOperator(newType);
            const conditionData = this.copyParameters({ ...condition, parameters });
            conditionData.type = negatedType;

            const query = this.createCondition(conditionData, condition.id, 0);
            this.insertNodeIntoTree(this.condition, query);

            Object.assign(
                condition,
                {
                    type: 'not',
                    field: null,
                    operator: null,
                    value: null,
                    parameters: null,
                },
            );
        },

        unwrapNot(condition, newType, parameters) {
            const innerCondition = condition.queries[0];
            const conditionData = this.copyParameters({ ...innerCondition, parameters });

            conditionData.type = newType;
            Object.assign(condition, conditionData);
            this.removeNodeFromTree(this.condition, innerCondition);
        },

        copyParameters({ field, type, operator, parameters, value }) {
            return { field, type, operator, parameters, value };
        },
    },
};