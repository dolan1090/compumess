import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import swCategoryDetailBase from 'src/module/sw-category/view/sw-category-detail-base';
import 'src/app/component/form/sw-select-rule-create';
import 'src/app/component/form/select/entity/sw-entity-multi-select';
import 'src/app/component/form/select/base/sw-select-base';
import 'src/app/component/form/field-base/sw-base-field';
import 'src/app/component/form/field-base/sw-block-field';
import 'src/app/component/form/select/base/sw-select-selection-list';
import categoryStore from 'src/module/sw-category/page/sw-category-detail/state';

const { Component } = Shopware;

// Component override
import '../../../src/module/sw-category/extension/sw-category-detail-base';

Shopware.Component.register('sw-category-detail-base', swCategoryDetailBase);

describe('module/sw-category/view/sw-category-detail-base', () => {
    let wrapper;

    const categoryMock = {
        navigationSalesChannels: [],
        footerSalesChannels: [],
        serviceSalesChannels: [],
        extensions: {
            swagDynamicAccessRules: []
        },
    };

    async function createWrapper() {
        const localVue = createLocalVue();
        localVue.use(Vuex);

        return shallowMount(await Component.build('sw-category-detail-base'), {
            localVue,
            stubs: {
                'sw-card': true,
                'sw-container': true,
                'sw-text-field': true,
                'sw-switch-field': true,
                'sw-single-select': true,
                'sw-entity-tag-select': true,
                'sw-category-detail-menu': true,
                'sw-category-detail-products': true,
                'sw-entity-single-select': true,
                'sw-category-entry-point-card': true,
                'sw-category-seo-form': true,
                'sw-base-field': await Component.build('sw-base-field'),
                'sw-select-base': await Component.build('sw-select-base'),
                'sw-block-field': await Component.build('sw-block-field'),
                'sw-entity-multi-select': await Component.build('sw-entity-multi-select'),
                'sw-select-selection-list': await Component.build('sw-select-selection-list'),
                'sw-select-rule-create': await Component.build('sw-select-rule-create'),
                'sw-select-result-list': {
                    template: '<div><slot name="before-item-list"></slot><slot></slot></div>'
                },
                'sw-icon': true,
                'sw-label': true,
                'sw-field-error': true,
                'sw-loader': true,
                'sw-alert': {
                    template: '<div class="sw-alert"><slot></slot></div>'
                }
            },
            mocks: {
                placeholder: () => {},
                $tc: key => key,
                $store: new Vuex.Store({
                    modules: {
                        swCategoryDetail: {
                            namespaced: true,
                            state: {
                                category: categoryMock
                            }
                        },
                        cmsPageState: {
                            namespaced: true
                        }
                    }
                })
            },
            propsData: {
                isLoading: false,
                manualAssignedProductsCount: 0
            },
            provide: {
                acl: {
                    can: () => true
                },
                repositoryFactory: {
                    create: () => ({
                        get: () => Promise.resolve(),
                        search: () => Promise.resolve()
                    })
                },
                feature: {
                    isActive: () => true
                },
                ruleConditionDataProviderService: {
                    getModuleTypes: () => []
                },
            }
        })
    }

    beforeAll(() => {
        Shopware.State.registerModule('swCategoryDetail', categoryStore);
    });

    beforeEach(() => {
        Shopware.State.commit('swCategoryDetail/setActiveCategory', {
            category: categoryMock
        });
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.destroy();
        }
    });

    it('should be a Vue.js component', async () => {
        wrapper = await createWrapper();
        expect(wrapper.vm).toBeTruthy();
    });

    it('should have a "create new rule" option', async () => {
        wrapper = await createWrapper();

        const selection = wrapper.find('.sw-select-rule-create .sw-select__selection');
        await selection.trigger('click');

        const addNewRuleOption = wrapper.find('.sw-select-rule-create__create-rule-item');
        expect(addNewRuleOption.exists()).toBe(true);

        expect(addNewRuleOption.text()).toBe('sw-select-rule-create.addNewRule');
    });
});
