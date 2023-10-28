import { shallowMount, createLocalVue } from "@vue/test-utils";
import Vuex from 'vuex';
const { Component } = Shopware;

import swLandingPageDetailBase from 'src/module/sw-category/view/sw-landing-page-detail-base';
import 'src/app/component/form/select/entity/sw-entity-multi-select';
import 'src/app/component/form/select/base/sw-select-base';
import 'src/app/component/form/field-base/sw-base-field';
import 'src/app/component/form/field-base/sw-block-field';
import 'src/app/component/form/sw-select-rule-create';
import 'src/app/component/form/select/base/sw-select-selection-list';

import categoryStore from 'src/module/sw-category/page/sw-category-detail/state';
import 'src/module/sw-cms/state/cms-page.state';

// Component override
import '../../../src/module/sw-category/extension/sw-landing-page-detail-base';

Shopware.Component.register('sw-landing-page-detail-base', swLandingPageDetailBase);

describe.only('module/sw-category/view/sw-landing-page-detail-base', () => {
    let wrapper;

    const landingPageMock = {
        "salesChannels": [],
        "extensions": {
            "swagDynamicAccessRules": []
        }
    }
    async function createWrapper() {
        const localVue = createLocalVue();
        localVue.use(Vuex);

        return shallowMount(await Component.build('sw-landing-page-detail-base'), {
            localVue,
            mocks: {
                $tc: key => key,
                $store: new Vuex.Store({
                    modules: {
                        swCategoryDetail: {
                            namespaced: true
                        },
                        cmsPageState: {
                            namespaced: true
                        }
                    }
                })
            },
            propsData: {
                isLoading: false
            },
            provide: {
                acl: {
                    can: () => true
                },
                repositoryFactory: {
                    create: () => ({
                        search: () => Promise.resolve()
                    })
                },
                feature: {
                    isActive: () => true
                },
                ruleConditionDataProviderService: {
                    getModuleTypes: () => []
                },
            },
            stubs: {
                'sw-container': {
                    template: '<div><slot></slot></div>'
                },
                'sw-card': {
                    template: '<div><slot></slot></div>'
                },
                'sw-switch-field': true,
                'sw-text-field': true,
                'sw-textarea-field': true,
                'sw-entity-tag-select': true,
                'sw-entity-multi-select': await Component.build('sw-entity-multi-select'),
                'sw-select-base': await Component.build('sw-select-base'),
                'sw-base-field': await Component.build('sw-base-field'),
                'sw-block-field': await Component.build('sw-block-field'),
                'sw-select-selection-list': await Component.build('sw-select-selection-list'),
                'sw-select-rule-create': await Component.build('sw-select-rule-create'),
                'sw-select-result-list': {
                    template: '<div><slot name="before-item-list"></slot><slot></slot></div>'
                },
                'sw-icon': true,
                'sw-label': true,
                'sw-field-error': true,
                'sw-loader': true
            }
        });
    }

    beforeAll(() => {
        Shopware.State.registerModule('swCategoryDetail', categoryStore)
    });

    beforeEach(() => {
        Shopware.State.commit('swCategoryDetail/setActiveLandingPage', {
            landingPage: landingPageMock
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
