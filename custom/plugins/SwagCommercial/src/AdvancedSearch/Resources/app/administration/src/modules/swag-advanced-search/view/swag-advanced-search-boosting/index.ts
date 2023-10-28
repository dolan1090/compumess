/**
 * @package buyers-experience
 */
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/src/core/data/repository.data';
import type CriteriaType from '@administration/src/core/data/criteria.data';
import licenseDecoratorHelper from '../../../../core/helper/license-decorator.helper';
import { LICENSE_TRAP_PRIMARY } from '../../../../constants';
import template from './swag-advanced-search-boosting.html';

const { Criteria } = Shopware.Data;

type BoostingData = {
    isLoading: boolean,
    boostings: Entity<'advanced_search_boosting'>[],
    searchTerm: string,
    showBoostingModal: boolean,
    boostingId: string,
    disableRouteParams: boolean,
}

type BoostingColumn = {
    property: string,
    label: string,
    primary: boolean,
    align?: string,
    type?: string,
}

export default {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('notification'),
    ],

    data(): BoostingData {
        return {
            isLoading: false,
            boostings: [],
            searchTerm: null,
            showBoostingModal: false,
            boostingId: null,
            disableRouteParams: true,
        };
    },

    computed: {
        boostingRepository(): RepositoryType<'advanced_search_boosting'> {
            return licenseDecoratorHelper(this.repositoryFactory.create('advanced_search_boosting'), LICENSE_TRAP_PRIMARY);
        },

        boostingCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('configId', this.advancedSearchConfigId));

            if (this.searchTerm) {
                criteria.setTerm(this.searchTerm);
            }

            return criteria;
        },

        boostingColumns(): BoostingColumn[] {
            return [
                {
                    property: 'name',
                    label: 'swag-advanced-search.boostingTab.column.name',
                    primary: true,
                },
                {
                    property: 'active',
                    label: 'swag-advanced-search.boostingTab.column.active',
                    primary: true,
                    align: 'center',
                },
                {
                    property: 'validFrom',
                    label: 'swag-advanced-search.boostingTab.column.validFrom',
                    primary: true,
                    type: 'Date',
                },
                {
                    property: 'validTo',
                    label: 'swag-advanced-search.boostingTab.column.validTo',
                    primary: true,
                    type: 'Date',
                },
            ];
        },

        salesChannelId(): string {
            return Shopware.State.getters['swAdvancedSearchState/salesChannelId'];
        },

        advancedSearchConfigId(): string {
            return Shopware.State.getters['swAdvancedSearchState/advancedSearchConfigId'];
        },
    },

    watch: {
        salesChannelId() {
            this.onChangePage({ page: 1, limit: 25 });
        },
    },

    methods: {
        async getList(): Promise<void> {
            this.isLoading = true;

            try {
                const response = await this.boostingRepository.search(this.boostingCriteria);
                this.boostings = response;
                this.total = response.total;
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        onChangeSearchTerm(searchTerm: string): void {
            this.searchTerm = searchTerm;
            this.onChangePage({ page: 1, limit: 25 });
        },

        onChangePage({ page, limit }: { page: number, limit: number }): void {
            this.page = page;
            this.limit = limit;
            this.getList();
        },

        toggleBoostingModal(): void {
            this.showBoostingModal = !this.showBoostingModal;
        },

        onEditBoosting(boosting: Entity<'advanced_search_boosting'>): void {
            this.boostingId = boosting.id;
            this.toggleBoostingModal();
        },

        onCancelBoosting(): void {
            this.boostingId = null;
            this.toggleBoostingModal();
        },

        async onFinishSaveBoosting(): Promise<void> {
            this.onCancelBoosting();
            await this.$nextTick();
            this.getList();
        },
    },
};
