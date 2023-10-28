/**
 * @package buyers-experience
 */
import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

export type EntityName = 'product' | 'category' | 'product_manufacturer';

export type AdvancedSearchState = {
    currentSearchType: EntityName,
    advancedSearchConfig: Entity<'advanced_search_config'>,
};

export default {
    namespaced: true,

    state(): AdvancedSearchState {
        return {
            currentSearchType: 'product',
            // @ts-expect-error The initial properties can be ignored
            advancedSearchConfig: {
                id: null,
                andLogic: true,
                minSearchLength: 2,
                esEnabled: true,
                salesChannelId: null,
                hitCount: {
                    product: {
                        maxSearchCount: 30,
                        maxSuggestCount: 10,
                    },
                    category: {
                        maxSearchCount: 30,
                        maxSuggestCount: 10,
                    },
                    product_manufacturer: {
                        maxSearchCount: 30,
                        maxSuggestCount: 10,
                    },
                },
            },
        };
    },

    mutations: {
        setCurrentSearchType(state: AdvancedSearchState, currentSearchType: EntityName): void {
            state.currentSearchType = currentSearchType;
        },

        setAdvancedSearchConfig(state: AdvancedSearchState, advancedSearchConfig: Entity<'advanced_search_config'>): void {
            state.advancedSearchConfig = advancedSearchConfig;
        },
    },

    getters: {
        esEnabled(state: AdvancedSearchState): boolean {
            return state.advancedSearchConfig.esEnabled;
        },

        salesChannelId(state: AdvancedSearchState): string {
            return state.advancedSearchConfig.salesChannelId;
        },

        advancedSearchConfigId(state: AdvancedSearchState): string {
            return state.advancedSearchConfig.id;
        },

        entity(state: AdvancedSearchState): EntityName {
            return state.currentSearchType;
        },

        entities(state: AdvancedSearchState, getters: {
            esEnabled: boolean,
            salesChannelId: string,
            advancedSearchConfigId: string,
            entity: EntityName,
        }): { value: string, label: string }[] {
            const options = [{
                value: 'product',
                label: Shopware.Application.view.i18n.tc('swag-advanced-search.entity.product'),
            }];

            return !getters.esEnabled ? options : options.concat([
                {
                    value: 'category',
                    label: Shopware.Application.view.i18n.tc('swag-advanced-search.entity.category'),
                },
                {
                    value: 'product_manufacturer',
                    label: Shopware.Application.view.i18n.tc('swag-advanced-search.entity.product_manufacturer'),
                },
            ]);
        },
    },
};
