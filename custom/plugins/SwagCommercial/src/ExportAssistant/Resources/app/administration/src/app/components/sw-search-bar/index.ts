/**
 * @package system-settings
 */
import template from './sw-search-bar.html.twig';

export default {
    template,

    inject: [
        'repositoryFactory',
        'searchTypeService',
        'criteriaGeneratorService',
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    data(): {
        showModalPreview: boolean,
        isLoadingCriteria: boolean,
        } {
        return {
            showModalPreview: false,
            isLoadingCriteria: false,
        };
    },

    methods: {
        loadTypeSearchResults(searchTerm) {
            const validTypes = ['export_assistant', 'search_assistant'];

            if (!validTypes.includes(this.currentSearchType)) {
                this.$super('loadTypeSearchResults', searchTerm);

                return;
            }

            this.turnOffContainers();
        },

        onSearchTermChange() {
            const validTypes = ['export_assistant', 'search_assistant'];

            if (!validTypes.includes(this.currentSearchType)) {
                this.$super('onSearchTermChange');

                return;
            }

            this.turnOffContainers();
        },

        onFocusInput() {
            const validTypes = ['export_assistant', 'search_assistant'];

            if (!validTypes.includes(this.currentSearchType)) {
                this.$super('onFocusInput');

                return;
            }

            this.turnOffContainers();
        },

        onEnter(event: {
            code: string,
        }) {
            if (event.code.toUpperCase() !== 'ENTER') {
                return;
            }

            if (this.searchTerm?.length <= 0) {
                return;
            }

            if (this.currentSearchType === 'search_assistant') {
                this.searchEntitiesByAI();

                return;
            }

            this.showModalPreview = this.currentSearchType === 'export_assistant';
        },

        onExport() {
            this.$router.push({ name: 'sw.import.export.index.export' });
        },

        async searchEntitiesByAI() {
            try {
                this.results = [];
                this.isLoadingCriteria = true;

                const { entity, criteria } = await this.criteriaGeneratorService.generate({ prompt: this.searchTerm });

                if (!this.searchTypeService.getTypeByName(entity)) {
                    this.createNotificationSuccess({
                        message: this.$tc('global.swag-search-assistant.messageEmpty'),
                    });

                    return;
                }

                criteria.setLimit(this.searchLimit);

                const repository = this.repositoryFactory.create(entity);
                const response = await repository.search(criteria, { ...Shopware.Context.api, inheritance: true });

                if (response.total <= 0) {
                    this.createNotificationSuccess({
                        message: this.$tc('global.swag-search-assistant.messageEmpty'),
                    });

                    return;
                }

                this.results = this.results.filter((result: { entity: string }) => {
                    return this.currentSearchType !== result.entity;
                });

                this.results = [...this.results, {
                    entity,
                    entities: response.slice(0, this.searchLimit) ?? [],
                    total: response.total,
                }];

                this.showResultsContainer = true;
            } catch (error) {
                if (error?.response?.status === 400) {
                    this.createNotificationError({
                        message: this.$tc('global.swag-search-assistant.messageMissingEntityOrCriteria'),
                    });

                    return;
                }

                this.createNotificationError({
                    message: this.$tc('global.swag-search-assistant.messageTryAgain'),
                });
            } finally {
                this.isLoadingCriteria = false;
            }
        },

        turnOffModalPreview() {
            this.showModalPreview = false;
        },

        turnOffContainers() {
            this.showTypeSelectContainer = false;
            this.showModuleFiltersContainer = false;
            this.showResultsContainer = false;
            this.showResultsSearchTrends = false;
            this.activeTypeListIndex = 0;
        },
    },
};
