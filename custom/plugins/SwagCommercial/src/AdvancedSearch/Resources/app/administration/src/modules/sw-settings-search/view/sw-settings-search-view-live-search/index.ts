/**
 * @package buyers-experience
 */
import template from './sw-settings-search-view-live-search.html.twig';

export default {
    template,

    computed: {
        esEnabled(): boolean {
            return Shopware.State.getters['swAdvancedSearchState/esEnabled'];
        },
    },
};
