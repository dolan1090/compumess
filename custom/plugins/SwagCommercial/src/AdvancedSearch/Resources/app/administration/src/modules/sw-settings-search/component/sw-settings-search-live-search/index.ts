/**
 * @package buyers-experience
 */
import type { EntityName } from '@advanced-search/modules/sw-settings-search/state/sw-advanced-search.state';
import template from './sw-settings-search-live-search.html.twig';
import './sw-settings-search-live-search.scss';

export default {
    template,

    inject: ['previewSearchService'],

    data(): {
        previewResults: [],
        previewSearchTerm: string,
        page: number,
        limit: number,
        total: number,
        showEmptyState: boolean,
        } {
        return {
            previewResults: [],
            previewSearchTerm: '',
            page: 1,
            limit: 25,
            total: 0,
            showEmptyState: false,
        };
    },

    computed: {
        isSearchEnable(): boolean {
            return Boolean(this.asSalesChannelId);
        },

        esEnabled(): boolean {
            return Shopware.State.getters['swAdvancedSearchState/esEnabled'];
        },

        entity(): EntityName {
            return Shopware.State.getters['swAdvancedSearchState/entity'];
        },

        entities(): { value: EntityName, label: string }[] {
            return Shopware.State.getters['swAdvancedSearchState/entities'];
        },

        asSalesChannelId(): string {
            return Shopware.State.getters['swAdvancedSearchState/salesChannelId'];
        },
    },

    methods: {
        searchOnStorefront(): void {
            this.salesChannelId = this.asSalesChannelId;
            this.$super('searchOnStorefront');
        },

        async onPreviewSearch(page = 1, limit = 25): Promise<void> {
            this.searchInProgress = true;

            if (this.previewSearchTerm.length <= 0) {
                this.searchInProgress = false;
                this.previewResults = [];

                return;
            }

            try {
                const response = await this.previewSearchService.search(
                    this.previewSearchTerm,
                    this.entity,
                    this.asSalesChannelId,
                    page,
                    limit,
                );

                this.total = response.meta.total;
                this.previewResults = response.data;
                this.searchInProgress = false;
                this.$emit('live-search-results-change', {
                    searchTerms: this.previewSearchTerm,
                    searchResults: this.previewResults,
                });
            } catch (error) {
                this.searchInProgress = false;
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                if (this.previewResults.length <= 0) {
                    this.showEmptyState = true;
                }
            }
        },

        onChangePreviewSearchTerm(): void {
            this.page = 1;
            this.limit = 25;
            this.total = 0;
            this.showEmptyState = false;
            this.onPreviewSearch(this.page, this.limit);
        },

        onChangeEntity(entity: EntityName): void {
            Shopware.State.commit('swAdvancedSearchState/setCurrentSearchType', entity);
            this.previewResults = [];
            this.previewSearchTerm = '';
            this.page = 1;
            this.limit = 25;
            this.total = 0;
            this.showEmptyState = false;
        },

        onChangePage({ page, limit }: { page: number, limit: number }): void {
            this.page = page;
            this.limit = limit;
            this.onPreviewSearch(this.page, this.limit);
        },
    },
};
