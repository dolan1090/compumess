import { shallowMount, createLocalVue } from '@vue/test-utils';

// Core components
import  swOrderLineItemsGrid from 'src/module/sw-order/component/sw-order-line-items-grid';
import 'src/app/component/data-grid/sw-data-grid';

// Component override
import '../../../../src/module/sw-order/component/sw-order-line-items-grid';

Shopware.Component.register('sw-order-line-items-grid', swOrderLineItemsGrid);

const createWrapper = async () => {
    const orderMock = {
        id: '1',
        versionId: '2',
        currency: {
            shortName: 'EUR',
            taxStatus: 'gross'
        },
        lineItems: [{
            id: '1',
            type: 'custom',
            label: 'Product item',
            quantity: 1,
            payload: {
                options: []
            },
            price: {
                quantity: 1,
                totalPrice: 200,
                unitPrice: 200,
                calculatedTaxes: [
                    {
                        price: 200,
                        tax: 40,
                        taxRate: 20
                    }
                ],
                taxRules: [
                    {
                        taxRate: 20,
                        percentage: 100
                    }
                ]
            }
        }],
        itemRounding: {
            interval: 0.01,
            decimals: 2
        }
    };

    const localVue =  createLocalVue();
    localVue.directive('tooltip', {
        bind(el, binding) {
            el.setAttribute('tooltip-message', binding.value.message);
        },
        inserted(el, binding) {
            el.setAttribute('tooltip-message', binding.value.message);
        },
        update(el, binding) {
            el.setAttribute('tooltip-message', binding.value.message);
        }
    });
    localVue.filter('currency', (currency) => currency);

    return shallowMount(await Shopware.Component.build('sw-order-line-items-grid'), {
        localVue,
        mocks: {
            $tc: key => key,
            $te: t => t,
            $device: { onResize: () => {} }
        },
        stubs: {
            'sw-container': true,
            'sw-card-filter': true,
            'sw-button': true,
            'sw-button-group': true,
            'sw-context-button': true,
            'sw-context-menu-item': true,
            'sw-data-grid': await Shopware.Component.build('sw-data-grid'),
            'sw-data-grid-settings': true,
            'sw-checkbox-field': true,
            'sw-product-variant-info': true,
            'router-link': true
        },
        propsData: {
            order: orderMock,
            context: Shopware.Context
        },
        provide: {
            acl: {
                can: () => true
            },
            orderService: {
                addProductToOrder: () => {
                    return {
                        get: () => Promise.resolve()
                    };
                },
                addCreditItemToOrder: () => {
                    return {
                        get: () => Promise.resolve()
                    };
                },
                addCustomLineItemToOrder: () => {
                    return {
                        get: () => Promise.resolve()
                    };
                }
            },
            repositoryFactory: {
                create: () => {
                    return {
                        get: () => Promise.resolve(),
                        search: () => Promise.resolve([
                            {
                                value: ['foo']
                            }
                        ])
                    };
                }
            },
            feature: {
                isActive: () => true
            }
        }
    });
};

describe('module/sw-order/component/sw-order-line-items-grid', () => {
    beforeAll(() => {
        Shopware.Service().register('cartStoreService', () => {
            return {
                getLineItemTypes: () => {
                    return Object.freeze({
                        PRODUCT: 'product',
                        CREDIT: 'credit',
                        CUSTOM: 'custom',
                        PROMOTION: 'promotion'
                    });
                }
            };
        });
    });

    it('should allow multiple inheritance for the block "sw_order_line_items_grid_grid_columns_label_link"', async () => {
        Shopware.Component.override('sw-order-line-items-grid', {
            template: `
            {% block sw_order_line_items_grid_grid_columns_label_link %}
                <h1 class="testing-headline" v-else-if="true === true">Test</h1>
                {% parent %}
            {% endblock %}`
        });

        const wrapper = await createWrapper();
        expect(wrapper.find('.testing-headline').exists()).toBe(true);
    });
});
