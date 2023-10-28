/* eslint-disable max-len */
import { shallowMount, createLocalVue } from '@vue/test-utils';
import swSettingsRuleAssignmentListing from 'src/module/sw-settings-rule/component/sw-settings-rule-assignment-listing';
import swSettingsRuleDetailAssignments from 'src/module/sw-settings-rule/view/sw-settings-rule-detail-assignments';
import 'src/app/component/entity/sw-entity-listing';
import 'src/app/component/data-grid/sw-data-grid';
import 'src/app/component/context-menu/sw-context-button';
import 'src/app/component/context-menu/sw-context-menu';
import 'src/app/component/context-menu/sw-context-menu-item';
import 'src/app/component/utils/sw-popover';
import EntityCollection from 'src/core/data/entity-collection.data';
import flushPromises from 'flush-promises';

// Component override
import '../../../src/module/sw-settings-rule/extension/sw-settings-rule-detail-assignments';
import Vuex from 'vuex';

Shopware.Component.extend('sw-settings-rule-assignment-listing', 'sw-entity-listing', swSettingsRuleAssignmentListing);
Shopware.Component.register('sw-settings-rule-detail-assignments', swSettingsRuleDetailAssignments);

function createEntityCollectionMock(entityName, items = []) {
    return new EntityCollection('/route', entityName, {}, {}, items, items.length);
}

async function createWrapper(entitiesWithResults = []) {
    const localVue = createLocalVue();
    localVue.directive('tooltip', {});

    return shallowMount(await Shopware.Component.build('sw-settings-rule-detail-assignments'), {
        localVue,

        mocks: {
            $tc: key => key,
            $te: key => key,
            $device: { onResize: () => {} }
        },

        stubs: {
            'sw-card': {
                template: '<div class="sw-card"><slot name="toolbar"></slot><slot name="grid"></slot></div>'
            },
            'sw-loader': true,
            'sw-empty-state': true,
            'sw-settings-rule-assignment-listing': await Shopware.Component.build('sw-settings-rule-assignment-listing'),
            'sw-entity-listing': await Shopware.Component.build('sw-entity-listing'),
            'sw-data-grid': await Shopware.Component.build('sw-data-grid'),
            'sw-pagination': true,
            'sw-context-button': await Shopware.Component.build('sw-context-button'),
            'sw-checkbox-field': true,
            'sw-context-menu-item': true,
            'sw-icon': true,
            'sw-button': true,
            'sw-field-error': true,
            'sw-card-filter': true,
            'router-link': {
                template: '<a class="router-link" :detail-route="to.name"><slot></slot></a>',
                props: ['to']
            }
        },
        propsData: {
            ruleId: 'uuid1',
            rule: {
                name: 'Test rule',
                priority: 7,
                description: 'Lorem ipsum',
                type: ''
            }
        },
        provide: {
            acl: {
                can: () => true
            },
            feature: {
                isActive: () => true
            },
            validationService: {},
            shortcutService: {
                startEventListener: () => {
                },
                stopEventListener: () => {
                }
            },

            ruleConditionDataProviderService: {},

            repositoryFactory: {
                create: (entityName) => {
                    return {
                        search: (_, api) => {
                            const entities = [
                                { name: 'Foo' },
                                { name: 'Bar' },
                                { name: 'Baz' }
                            ];

                            if (api.inheritance) {
                                entities.push({ name: 'Inherited' });
                            }

                            if (entitiesWithResults.includes(entityName)) {
                                return Promise.resolve(createEntityCollectionMock(entityName, entities));
                            }

                            return Promise.resolve(createEntityCollectionMock(entityName));
                        }
                    };
                }
            }
        }
    });
}

describe('src/module/sw-settings-rule/view/sw-settings-rule-detail-assignments', () => {
    it('should be a Vue.JS component', async () => {
        const wrapper = await createWrapper();

        expect(wrapper.vm).toBeTruthy();
    });

    it('should prepare association entities list', async () => {
        const wrapper = await createWrapper([]);

        expect(wrapper.vm.associationEntities).toEqual(
            expect.arrayContaining([
                expect.objectContaining({
                    associationName: 'swagDynamicAccessProducts',
                    entityName: 'product',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expect.any(Array)
                }),
                expect.objectContaining({
                    associationName: 'swagDynamicAccessCategories',
                    entityName: 'category',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expect.any(Array)
                }),
                expect.objectContaining({
                    associationName: 'swagDynamicAccessLandingPages',
                    entityName: 'landing_page',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expect.any(Array)
                })
            ])
        );
    });

    it('should try to load and assign entity data for defined entities', async () => {
        const wrapper = await createWrapper([
            'product',
            'category',
            'landing_page',
        ]);
        await flushPromises();

        const expectedEntityCollectionResult = expect.arrayContaining([
            expect.objectContaining({ name: 'Foo' }),
            expect.objectContaining({ name: 'Bar' }),
            expect.objectContaining({ name: 'Baz' })
        ]);

        expect(wrapper.vm.associationEntities).toEqual(
            expect.arrayContaining([
                expect.objectContaining({
                    id: 'swagDynamicAccessProducts',
                    entityName: 'product',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expectedEntityCollectionResult
                }),
                expect.objectContaining({
                    id: 'swagDynamicAccessCategories',
                    entityName: 'category',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expectedEntityCollectionResult
                }),
                expect.objectContaining({
                    id: 'swagDynamicAccessLandingPages',
                    entityName: 'landing_page',
                    detailRoute: expect.any(String),
                    repository: expect.any(Object),
                    gridColumns: expect.any(Array),
                    criteria: expect.any(Function),
                    loadedData: expectedEntityCollectionResult
                })
            ])
        );
    });

    it('should render an entity-listing for each entity when all entities have results', async () => {
        const wrapper = await createWrapper([
            'product',
            'category',
            'landing_page',
        ]);
        await flushPromises();

        // Expect entity listings to be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessProducts .router-link').exists()).toBeTruthy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessCategories .router-link').exists()).toBeTruthy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessLandingPages .router-link').exists()).toBeTruthy();

        // Empty states should not be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessProducts').exists()).toBeFalsy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessCategories').exists()).toBeFalsy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessLandingPages').exists()).toBeFalsy();

        // Loader should not be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-loader').exists()).toBeFalsy();
    });

    it('should render an entity-listing also if no assignment is found', async () => {
        const wrapper = await createWrapper([]);
        await flushPromises();

        // Expect entity listings to not be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessProducts .router-link').exists()).toBeFalsy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessCategories .router-link').exists()).toBeFalsy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessLandingPages .router-link').exists()).toBeFalsy();

        // Expect empty states to be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessProducts').exists()).toBeTruthy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessCategories').exists()).toBeTruthy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state-swagDynamicAccessLandingPages').exists()).toBeTruthy();

        // Loader should not be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-loader').exists()).toBeFalsy();
    });

    it('should render an empty-state when none of the associated entities returns a result', async () => {
        const wrapper = await createWrapper();
        await flushPromises();

        expect(wrapper.find('.sw-settings-rule-detail-assignments__entity-empty-state').exists()).toBeTruthy();
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-loader').exists()).toBeFalsy();
    });

    it('should render names of product variants', async () => {
        const wrapper = await createWrapper(['product']);
        await flushPromises();

        // expect entity listing for products to be present
        expect(wrapper.find('.sw-settings-rule-detail-assignments__card-swagDynamicAccessProducts .router-link').exists()).toBeTruthy();

        const productAssignments = wrapper.findAll('.sw-settings-rule-detail-assignments__entity-listing-swagDynamicAccessProducts .sw-data-grid__cell--name');

        // expect the right amount of items
        expect(productAssignments.length).toBe(4);

        const validNames = ['Foo', 'Bar', 'Baz', 'Inherited'];

        // expect the correct names of the products
        productAssignments.wrappers.forEach((assignment, index) => {
            expect(assignment.text()).toBe(validNames[index]);
        });
    });

    it('should have the right link inside the template', async () => {
        const wrapper = await createWrapper([
            'category',
            'landing_page'
        ]);
        await flushPromises();

        const categoryListing = wrapper.find('.sw-settings-rule-detail-assignments__entity-listing-swagDynamicAccessCategories .sw-data-grid__cell--name  .router-link');
        const landingPageListing = wrapper.find('.sw-settings-rule-detail-assignments__entity-listing-swagDynamicAccessLandingPages .sw-data-grid__cell--name  .router-link');

        // expect entity listing to exist
        expect(categoryListing.exists()).toBe(true);
        expect(landingPageListing.exists()).toBe(true);


        const categoryDetailRouteAttribute = categoryListing.attributes('detail-route');
        const landingPageDetailRouteAttribute = landingPageListing.attributes('detail-route');

        // expect detail-route attribute to be correct
        expect(categoryDetailRouteAttribute).toBe('sw.category.detail.base');
        expect(landingPageDetailRouteAttribute).toBe('sw.category.landingPageDetail.base');
    });
});
