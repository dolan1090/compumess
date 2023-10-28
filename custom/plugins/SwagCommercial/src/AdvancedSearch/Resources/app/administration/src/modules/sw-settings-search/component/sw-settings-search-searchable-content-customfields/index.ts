/**
 * @package buyers-experience
 */
import type CriteriaType from '@administration/src/core/data/criteria.data';
import type { EntityName } from '@advanced-search/modules/sw-settings-search/state/sw-advanced-search.state';
import template from './sw-settings-search-searchable-content-customfields.html.twig';

const { Criteria } = Shopware.Data;

export default {
    template,

    computed: {
        customFieldFilteredCriteria(): CriteriaType {
            const criteria = this.$super('customFieldFilteredCriteria');

            if (!this.esEnabled) {
                return criteria;
            }

            criteria.addFilter(Criteria.equals('customFieldSet.relations.entityName', this.entity));
            criteria.addSorting(Criteria.sort('config.customFieldPosition'));

            return criteria;
        },

        salesChannelId(): string {
            return Shopware.State.getters['swAdvancedSearchState/salesChannelId'];
        },

        esEnabled(): boolean {
            return Shopware.State.getters['swAdvancedSearchState/esEnabled'];
        },

        entity(): EntityName {
            return Shopware.State.getters['swAdvancedSearchState/entity'];
        },
    },

    watch: {
        salesChannelId(): void {
            this.createdComponent();
        },

        esEnabled(): void {
            this.createdComponent();
        },
    },

    methods: {
        onInlineEditItem(item): void {
            this.$refs.customGrid.onDbClickCell(item);
        },
    },
};
