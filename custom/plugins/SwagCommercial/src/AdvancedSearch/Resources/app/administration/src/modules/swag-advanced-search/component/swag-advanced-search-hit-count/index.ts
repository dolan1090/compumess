/**
 * @package buyers-experience
 */
import template from './swag-advanced-search-hit-count.html';

const { mapState } = Shopware.Component.getComponentHelper();

export default {
    template,

    computed: {
        ...mapState('swAdvancedSearchState', [
            'advancedSearchConfig',
        ]),
    },
};
