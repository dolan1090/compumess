/**
 * @package buyers-experience
 */
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type RepositoryType from '@administration/src/core/data/repository.data';
import type CriteriaType from '@administration/src/core/data/criteria.data';
import licenseDecoratorHelper from '../../../../core/helper/license-decorator.helper';
import { LICENSE_TRAP_PRIMARY } from '../../../../constants';
import template from './sw-settings-search.html.twig';
import './sw-settings-search.scss';

const { Defaults } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState } = Shopware.Component.getComponentHelper();

export default {
    template,

    data(): {
        salesChannels: Entity<'sales_channel'>[],
        salesChannelId: string | null,
        } {
        return {
            salesChannels: [],
            salesChannelId: null,
        };
    },

    computed: {
        ...mapState('swAdvancedSearchState', [
            'advancedSearchConfig',
        ]),

        salesChannelRepository(): RepositoryType<'sales_channel'> {
            return licenseDecoratorHelper(this.repositoryFactory.create('sales_channel'), 'ADVANCED_SEARCH-1770225');
        },

        salesChannelCriteria(): CriteriaType {
            const criteria = new Criteria(1, null);

            return criteria;
        },

        advancedSearchConfigRepository(): RepositoryType<'advanced_search_config'> {
            return licenseDecoratorHelper(this.repositoryFactory.create('advanced_search_config'), LICENSE_TRAP_PRIMARY);
        },

        advancedSearchConfigCriteria(): CriteriaType {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('salesChannelId', this.salesChannelId));

            return criteria;
        },

        selectedSalesChannel(): Entity<'sales_channel'> | null {
            if (this.salesChannels.length <= 0) {
                return null;
            }

            this.salesChannels.sort((a: Entity<'sales_channel'>, b: Entity<'sales_channel'>) => {
                const nameA = a.translated.name.toUpperCase();
                const nameB = b.translated.name.toUpperCase();

                if (a.typeId === Defaults.storefrontSalesChannelTypeId && nameA < nameB) {
                    return -1;
                }

                return 1;
            });

            return this.salesChannels[0];
        },

        esEnabled(): boolean {
            return Shopware.State.getters['swAdvancedSearchState/esEnabled'];
        },
    },

    watch: {
        esEnabled: {
            handler(newValue: boolean) {
                if (newValue === true) {
                    return;
                }

                this.getProductSearchConfigs();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent(): Promise<void> {
            await this.getSalesChannel();
            this.salesChannelId = this.selectedSalesChannel?.id ?? this.salesChannelId;
            this.getAdvancedSearchConfig();
            this.onTabChange();
        },

        async getSalesChannel() {
            this.isLoading = true;

            try {
                const response = await this.salesChannelRepository.search(this.salesChannelCriteria);
                this.salesChannels = response;
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        async getAdvancedSearchConfig(): Promise<void> {
            this.isLoading = true;

            try {
                const response = await this.advancedSearchConfigRepository.search(this.advancedSearchConfigCriteria);
                Shopware.State.commit('swAdvancedSearchState/setAdvancedSearchConfig', response.first());
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        onChangeSalesChannelId(salesChannelId: string | null): void {
            this.salesChannelId = salesChannelId;

            this.getAdvancedSearchConfig();
        },

        async onSaveSearchSettings(): Promise<void> {
            this.isLoading = true;

            try {
                await this.advancedSearchConfigRepository.save(this.advancedSearchConfig);
                this.createNotificationSuccess({
                    message: this.$tc('sw-settings-search.notification.saveSuccess'),
                });
                this.isSaveSuccessful = true;
                this.getAdvancedSearchConfig();
            } catch (error) {
                this.createNotificationError({
                    message: error.message,
                });
            } finally {
                this.isLoading = false;
            }
        },

        onLiveSearchResultsChanged() {
            this.searchTerms = '';
            this.searchResults = null;
        },
    },
};
