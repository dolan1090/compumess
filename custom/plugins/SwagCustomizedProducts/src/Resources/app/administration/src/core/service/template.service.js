const { createId } = Shopware.Utils;

/**
 * Helper service for Customized Product Templates
 * @class
 */
class SwagCustomizedProductsTemplateService {
    constructor() {
        this.mappingTable = {};
    }

    /**
     * Duplicates a given template and sets specific values
     *
     * @param {Entity} template
     * @param {String} copySuffix
     * @return {Promise}
     */
    duplicateTemplate(template, copySuffix = '') {
        const overrideOptions = template.options.reduce(this.reduceOverrideOptions.bind(this), []);
        const overrideExclusions = template.exclusions.reduce(this.reduceOverrideExclusions.bind(this), []);

        this.mappingTable = {};
        const overrides = {
            id: createId(),
            internalName: `${template.internalName} ${copySuffix}`,
            displayName: `${template.displayName} ${copySuffix}`,
            active: false,
            options: overrideOptions,
            exclusions: overrideExclusions,
        };

        return Shopware.Service('repositoryFactory')
            .create('swag_customized_products_template')
            .clone(template.id, Shopware.Context.api, { overwrites: overrides });
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} option
     * @return {Array}
     *
     * @private
     */
    reduceOverrideOptions(accumulator, option) {
        const newId = createId();
        this.mappingTable[option.id] = newId;

        option.id = newId;
        option.itemNumber = '';
        option.values = option.values.reduce(this.reduceOverrideOptionValues.bind(this), []);
        accumulator.push(option);

        return accumulator;
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} value
     * @return {Array}
     *
     * @private
     */
    reduceOverrideOptionValues(accumulator, value) {
        const newId = createId();
        this.mappingTable[value.id] = newId;

        value.id = newId;
        value.itemNumber = '';
        accumulator.push(value);

        return accumulator;
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} exclusion
     * @return {Array}
     *
     * @private
     */
    reduceOverrideExclusions(accumulator, exclusion) {
        const newId = createId();
        this.mappingTable[exclusion.id] = newId;

        exclusion.id = newId;
        exclusion.templateId = '';
        exclusion.conditions = exclusion.conditions.reduce(this.reduceOverrideConditions.bind(this), []);
        accumulator.push(exclusion);

        return accumulator;
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} condition
     * @return {Array}
     *
     * @private
     */
    reduceOverrideConditions(accumulator, condition) {
        const newId = createId();
        this.mappingTable[condition.id] = newId;

        condition.id = newId;
        condition.templateExclusionId = this.mappingTable[condition.templateExclusionId];
        condition.templateOptionId = this.mappingTable[condition.templateOptionId];
        condition.templateOptionValues = condition.templateOptionValues.reduce(
            this.reduceOverrideConditionValues.bind(this),
            [],
        );
        accumulator.push(condition);

        return accumulator;
    }

    /**
     * @param {Array} accumulator
     * @param {Entity} value
     * @return {Array}
     *
     * @private
     */
    reduceOverrideConditionValues(accumulator, value) {
        value.templateExclusionConditionId = this.mappingTable[value.templateExclusionConditionId];
        value.templateOptionValueId = this.mappingTable[value.templateOptionValueId];
        accumulator.push(value);

        return accumulator;
    }
}

export default SwagCustomizedProductsTemplateService;
