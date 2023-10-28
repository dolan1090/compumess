import type CriteriaType from '@administration/core/data/criteria.data';
import {DocumentTypes} from '../../../../../../type/types.d';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * @package checkout
 */
export default Component.wrapComponentConfig({
    computed: {
        documentTypeCriteria(): CriteriaType {
            const criteria = this.$super('documentTypeCriteria');

            criteria.addFilter(Criteria.not('AND', [Criteria.equals('technicalName', DocumentTypes.PARTIAL_CANCELLATION)]));

            return criteria;
        }
    },
});
