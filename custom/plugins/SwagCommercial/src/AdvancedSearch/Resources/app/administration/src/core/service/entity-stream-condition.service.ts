type EntityFilterType = {
    identifier: string,
    label: string,
};

/**
 *
 * @private
 *
 * @package buyers-experience
 */
export default class EntityStreamConditionService {
    static serviceName = 'entityStreamConditionService';

    entityFilterTypes: {
        equals: EntityFilterType,
        equalsAny: EntityFilterType,
        contains: EntityFilterType,
        lessThan: EntityFilterType,
        greaterThan: EntityFilterType,
        lessThanEquals: EntityFilterType,
        greaterThanEquals: EntityFilterType,
        notEquals: EntityFilterType,
        notEqualsAny: EntityFilterType,
        notContains: EntityFilterType,
        range: EntityFilterType,
        not: EntityFilterType,
    };

    operatorSets: {
        boolean: EntityFilterType[],
        string: EntityFilterType[],
        date: EntityFilterType[],
        uuid: EntityFilterType[],
        int: EntityFilterType[],
        float: EntityFilterType[],
        object: EntityFilterType[],
        default: EntityFilterType[],
    };

    constructor() {
        this.entityFilterTypes = {
            equals: {
                identifier: 'equals',
                label: 'swag-advanced-search.entityStream.filter.type.equals',
            },
            equalsAny: {
                identifier: 'equalsAny',
                label: 'swag-advanced-search.entityStream.filter.type.equalsAny',
            },
            contains: {
                identifier: 'contains',
                label: 'swag-advanced-search.entityStream.filter.type.contains',
            },
            lessThan: {
                identifier: 'lessThan',
                label: 'swag-advanced-search.entityStream.filter.type.lessThan',
            },
            greaterThan: {
                identifier: 'greaterThan',
                label: 'swag-advanced-search.entityStream.filter.type.greaterThan',
            },
            lessThanEquals: {
                identifier: 'lessThanEquals',
                label: 'swag-advanced-search.entityStream.filter.type.lessThanEquals',
            },
            greaterThanEquals: {
                identifier: 'greaterThanEquals',
                label: 'swag-advanced-search.entityStream.filter.type.greaterThanEquals',
            },
            notEquals: {
                identifier: 'notEquals',
                label: 'swag-advanced-search.entityStream.filter.type.notEquals',
            },
            notEqualsAny: {
                identifier: 'notEqualsAny',
                label: 'swag-advanced-search.entityStream.filter.type.notEqualsAny',
            },
            notContains: {
                identifier: 'notContains',
                label: 'swag-advanced-search.entityStream.filter.type.notContains',
            },
            range: {
                identifier: 'range',
                label: 'swag-advanced-search.entityStream.filter.type.range',
            },
            not: {
                identifier: 'not',
                label: 'swag-advanced-search.entityStream.filter.type.not',
            },
        };

        this.operatorSets = {
            boolean: [
                this.entityFilterTypes.equals,
            ],
            string: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.equalsAny,
                this.entityFilterTypes.notEqualsAny,
                this.entityFilterTypes.contains,
                this.entityFilterTypes.notContains,
            ],
            date: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.greaterThan,
                this.entityFilterTypes.greaterThanEquals,
                this.entityFilterTypes.lessThan,
                this.entityFilterTypes.lessThanEquals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.range,
            ],
            uuid: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.equalsAny,
                this.entityFilterTypes.notEqualsAny,
            ],
            int: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.greaterThan,
                this.entityFilterTypes.greaterThanEquals,
                this.entityFilterTypes.lessThan,
                this.entityFilterTypes.lessThanEquals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.range,
            ],
            float: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.greaterThan,
                this.entityFilterTypes.greaterThanEquals,
                this.entityFilterTypes.lessThan,
                this.entityFilterTypes.lessThanEquals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.range,
            ],
            object: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.greaterThan,
                this.entityFilterTypes.greaterThanEquals,
                this.entityFilterTypes.lessThan,
                this.entityFilterTypes.lessThanEquals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.range,
            ],
            default: [
                this.entityFilterTypes.equals,
                this.entityFilterTypes.notEquals,
                this.entityFilterTypes.equalsAny,
                this.entityFilterTypes.notEqualsAny,
            ],
        };
    }

    getConditions() {
        return [
            {
                type: 'entityStreamFilter',
                component: 'swag-advanced-search-entity-stream-filter',
                label: 'Entity',
            },
        ];
    }

    getAndContainerData() {
        return { type: 'multi', field: null, parameters: null, operator: 'AND' };
    }

    isAndContainer(condition: { type: string, operator: string }) {
        return condition.type === 'multi' && condition.operator === 'AND';
    }

    getOrContainerData() {
        return { type: 'multi', field: null, parameters: null, operator: 'OR' };
    }

    isOrContainer(condition: { type: string, operator: string }) {
        return condition.type === 'multi' && condition.operator === 'OR';
    }

    getPlaceholderData() {
        return { type: 'equals', field: 'id', parameters: null, operator: null };
    }

    getComponentByCondition(condition: { type: string, operator: string }) {
        if (this.isAndContainer(condition)) {
            return 'sw-condition-and-container';
        }

        if (this.isOrContainer(condition)) {
            return 'sw-condition-or-container';
        }

        return 'swag-advanced-search-entity-stream-filter';
    }

    getOperatorSet(type: string) {
        if (!Shopware.Utils.types.isString(type) || type === '') {
            return this.operatorSets.default;
        }

        return this.operatorSets[type] || this.operatorSets.default;
    }

    getOperator(type: string) {
        return this.entityFilterTypes[type];
    }

    negateOperator(type: string) {
        switch (type) {
            case 'equals':
                return this.entityFilterTypes.notEquals;
            case 'notEquals':
                return this.entityFilterTypes.equals;
            case 'equalsAny':
                return this.entityFilterTypes.notEqualsAny;
            case 'notEqualsAny':
                return this.entityFilterTypes.equalsAny;
            case 'contains':
                return this.entityFilterTypes.notContains;
            case 'notContains':
                return this.entityFilterTypes.contains;
            default:
                return this.entityFilterTypes[type] || null;
        }
    }

    isNegatedType(type: string) {
        return [
            this.entityFilterTypes.notContains.identifier,
            this.entityFilterTypes.notEqualsAny.identifier,
            this.entityFilterTypes.notEquals.identifier,
        ].includes(type);
    }

    isRangeType(type: string) {
        return [
            this.entityFilterTypes.lessThan.identifier,
            this.entityFilterTypes.lessThanEquals.identifier,
            this.entityFilterTypes.greaterThan.identifier,
            this.entityFilterTypes.greaterThanEquals.identifier,
            this.entityFilterTypes.range.identifier,
        ].includes(type);
    }
}
