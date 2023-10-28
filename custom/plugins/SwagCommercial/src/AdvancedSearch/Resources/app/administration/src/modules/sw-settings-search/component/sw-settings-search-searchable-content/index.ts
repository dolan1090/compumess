/**
 * @package buyers-experience
 */
import type RepositoryType from '@administration/src/core/data/repository.data';
import type CriteriaType from '@administration/src/core/data/criteria.data';
import type { EntityName } from '@advanced-search/modules/sw-settings-search/state/sw-advanced-search.state';
import licenseDecoratorHelper from '../../../../core/helper/license-decorator.helper';
import { LICENSE_TRAP_PRIMARY as TRAP_PRIMARY } from '../../../../constants';
import template from './sw-settings-search-searchable-content.html.twig';
import './sw-settings-search-searchable-content.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { mapState } = Component.getComponentHelper();

export default {
    template,

    data(): {
        page: number,
        limit: number,
        total: number,
        showResetModalConfirm: boolean,
        } {
        return {
            page: 1,
            limit: 25,
            total: 0,
            showResetModalConfirm: false,
        };
    },

    computed: {
        ...mapState('swAdvancedSearchState', [
            'currentSearchType',
            'advancedSearchConfig',
        ]),

        productSearchFieldRepository(): RepositoryType<'advanced_search_config_field'> {
            if (!this.esEnabled) {
                return licenseDecoratorHelper(this.repositoryFactory.create('product_search_config_field'), TRAP_PRIMARY);
            }

            return licenseDecoratorHelper(this.repositoryFactory.create('advanced_search_config_field'), TRAP_PRIMARY);
        },

        advancedSearchConfigFieldCriteria(): CriteriaType {
            if (!this.esEnabled) {
                return this.productSearchFieldCriteria;
            }

            const criteria = new Criteria(this.page, this.limit);
            criteria.addSorting(Criteria.sort('field', 'ASC'));
            criteria.addFilter(Criteria.equals('config.salesChannelId', this.advancedSearchConfig.salesChannelId));
            criteria.addFilter(Criteria.equals('entity', this.currentSearchType));

            if (this.defaultTab === this.tabNames.generalTab) {
                criteria.addFilter(Criteria.equals('customFieldId', null));
            } else {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [Criteria.equals('customFieldId', null)],
                ));
            }

            return criteria;
        },

        productSearchFieldCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.addFilter(Criteria.equals('searchConfigId', this.searchConfigId || null));
            criteria.addSorting(Criteria.sort('field', 'ASC'));

            if (this.defaultTab === this.tabNames.generalTab) {
                criteria.addFilter(Criteria.equals('customFieldId', null));
            }

            if (this.defaultTab === this.tabNames.customTab) {
                criteria.addFilter(Criteria.not(
                    'AND',
                    [Criteria.equals('customFieldId', null)],
                ));
            }

            return criteria;
        },

        esEnabled(): boolean {
            return Shopware.State.getters['swAdvancedSearchState/esEnabled'];
        },

        entities(): { value: EntityName, label: string }[] {
            return Shopware.State.getters['swAdvancedSearchState/entities'];
        },
    },

    watch: {
        'advancedSearchConfig.salesChannelId'(): void {
            this.onPageChange({ page: 1, limit: 25 });
        },

        'advancedSearchConfig.esEnabled'(): void {
            Shopware.State.commit('swAdvancedSearchState/setCurrentSearchType', 'product');

            this.onPageChange({ page: 1, limit: 25 });
        },

        currentSearchType(): void {
            this.onPageChange({ page: 1, limit: 25 });
        },
    },

    created(): void {
        this.createdComponent();
    },

    methods: {
        createdComponent(): void {
            this.getProductSearchFieldsList();
        },

        getAdvancedConfigFields(): void {
            const configFields = this.searchConfigFields.map(item => {
                return {
                    entity: item.entity,
                    field: item.field,
                    ranking: item.ranking,
                };
            });
            const advancedFieldConfigs = [...new Set(configFields)].map((value: {
                entity: string,
                field: string,
                ranking: number,
            }) => {
                return {
                    label: this.getLabelSearchConfigField(value.field, value.entity),
                    value: value.field,
                    defaultConfigs: {
                        searchable: false,
                        ranking: value.ranking,
                        tokenize: false,
                    },
                };
            });

            this.fieldConfigs = [
                ...advancedFieldConfigs,
            ];
        },

        onChangeEntity(entity: EntityName): void {
            Shopware.State.commit('swAdvancedSearchState/setCurrentSearchType', entity);

            if (this.currentSearchType !== 'product') {
                this.defaultTab = this.tabNames.generalTab;
                this.onChangeTab(this.tabNames.generalTab);
            }
        },

        onPageChange({ page, limit }: { page: number, limit: number }): void {
            this.page = page;
            this.limit = limit;

            this.getProductSearchFieldsList();
        },

        onResetToDefault(): void {
            this.toggleResetConfirmModal();
            this.$super('onResetToDefault');
        },

        toggleResetConfirmModal(): void {
            this.showResetModalConfirm = !this.showResetModalConfirm;
        },

        async getProductSearchFieldsList(): Promise<void> {
            this.isLoading = true;

            try {
                // eslint-disable-next-line max-len
                this.searchConfigFields = await this.productSearchFieldRepository.search(this.advancedSearchConfigFieldCriteria);

                if (this.searchConfigFields.length > 0) {
                    this.getAdvancedConfigFields();
                    this.total = this.searchConfigFields.total;
                }

                this.isEnabledReset = !this.total;
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-search.notification.loadError'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        getLabelSearchConfigField(field: string, entity?: string): string {
            const snippetPath = !this.esEnabled
                ? `swag-advanced-search.searchConfigField.${field}`
                : `swag-advanced-search.advancedSearchConfigField.${entity}.${field}`;

            return this.$te(snippetPath) ? this.$tc(snippetPath) : field;
        },

        createNewConfigItem() {
            if (!this.esEnabled) {
                return this.$super('createNewConfigItem');
            }

            const newItem = this.productSearchFieldRepository.create();

            newItem.configId = this.advancedSearchConfig.id;
            newItem.searchable = false;
            newItem.ranking = 0;
            newItem.entity = this.currentSearchType;
            newItem.field = '';
            newItem.tokenize = false;

            return newItem;
        },

        onChangeTab(tabContent) {
            this.page = 1;
            this.defaultTab = tabContent;
            this.loadData();
        },
    },
};
