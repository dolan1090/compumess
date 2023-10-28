import {shallowMount} from "@vue/test-utils";
import swProductCategoryForm from 'src/module/sw-product/component/sw-product-category-form';
import 'src/app/component/utils/sw-inherit-wrapper';
import 'src/app/component/form/sw-switch-field';
import 'src/app/component/form/sw-select-rule-create';
import 'src/app/component/form/select/entity/sw-entity-multi-select';
import 'src/app/component/form/select/base/sw-select-base';
import 'src/app/component/form/sw-checkbox-field';
import 'src/app/component/form/field-base/sw-base-field';
import 'src/app/component/form/field-base/sw-block-field';
import 'src/app/component/form/select/base/sw-select-selection-list';
import 'src/app/component/form/field-base/sw-field-error';
import Vuex from 'vuex';

// Core components
import 'src/module/sw-product/component/sw-product-category-form';
import productStore from 'src/module/sw-product/page/sw-product-detail/state';

// Component override
import '../../../src/module/sw-product/extension/sw-product-category-form';

Shopware.Component.register('sw-product-category-form', swProductCategoryForm);

async function createWrapper(productEntityOverride, parentProductOverride) {
    const productEntity =
        {
            metaTitle: 'Product1',
            id: 'productId1',
            isNew: () => false,
            extensions: {
                swagDynamicAccessRules: [
                    {
                        id: 'lul',
                        title: 'Custom rule'
                    }
                ]
            },
            ...productEntityOverride
        };

    const parentProduct = {
        id: 'productId',
        ...parentProductOverride
    };

    return shallowMount(await Shopware.Component.build('sw-product-category-form'), {
        mocks: {
            $route: {
                name: 'sw.product.detail.base',
                params: {
                    id: 1
                }
            },
            $store: new Vuex.Store({
                modules: {
                    swProductDetail: {
                        ...productStore,
                        state: {
                            ...productStore.state,
                            product: productEntity,
                            parentProduct,
                            loading: {
                                product: false,
                                media: false
                            },
                            advancedModeSetting: {
                                value: {
                                    settings: [
                                        {
                                            key: 'visibility_structure',
                                            label: 'sw-product.detailBase.cardTitleVisibilityStructure',
                                            enabled: true,
                                            name: 'general'
                                        }
                                    ],
                                    advancedMode: {
                                        enabled: true,
                                        label: 'sw-product.general.textAdvancedMode'
                                    }
                                }
                            }
                        }
                    }
                }
            }),
            $tc: key => key
        },
        provide: {
            feature: {
                isActive: () => true
            },
            systemConfigApiService: {},
            repositoryFactory: {
                create: () => ({
                    get: () => Promise.resolve([]),
                    search: () => Promise.resolve([])
                })
            },
            ruleConditionDataProviderService: {
                getModuleTypes: () => []
            },
        },
        stubs: {
            'sw-container': {
                template: '<div><slot></slot></div>'
            },
            'sw-inherit-wrapper': await Shopware.Component.build('sw-inherit-wrapper'),
            'sw-modal': true,
            'sw-multi-tag-select': true,
            'sw-switch-field': await Shopware.Component.build('sw-switch-field'),
            'sw-checkbox-field': await Shopware.Component.build('sw-checkbox-field'),
            'sw-base-field': await Shopware.Component.build('sw-base-field'),
            'sw-field-error': await Shopware.Component.build('sw-field-error'),
            'sw-category-tree-field': true,
            'sw-entity-tag-select': true,
            'sw-product-visibility-select': true,
            'sw-help-text': true,
            'sw-inheritance-switch': true,
            'sw-icon': true,
            'sw-select-rule-create': await Shopware.Component.build('sw-select-rule-create'),
            'sw-entity-multi-select': await Shopware.Component.build('sw-entity-multi-select'),
            'sw-select-base': await Shopware.Component.build('sw-select-base'),
            'sw-block-field': await Shopware.Component.build('sw-block-field'),
            'sw-select-selection-list': await Shopware.Component.build('sw-select-selection-list'),
            'sw-label': true,
            'sw-select-result-list': {
                template: '<div><slot name="before-item-list"></slot><slot></slot></div>'
            },
            'sw-loader': true
        }
    })
}

describe('module/sw-product/component/sw-product-category-form', function () {
    let wrapper;

    afterEach(() => {
        if (wrapper) wrapper.destroy();
    })

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
