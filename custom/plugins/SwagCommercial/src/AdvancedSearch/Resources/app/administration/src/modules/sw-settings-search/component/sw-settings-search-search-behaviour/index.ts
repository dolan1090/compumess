/**
 * @package buyers-experience
 */
import template from './sw-settings-search-search-behaviour.html.twig';
import './sw-settings-search-search-behaviour.scss';

const { mapState } = Shopware.Component.getComponentHelper();

export default {
    template,

    computed: {
        ...mapState('swAdvancedSearchState', [
            'advancedSearchConfig',
        ]),
    },
};
