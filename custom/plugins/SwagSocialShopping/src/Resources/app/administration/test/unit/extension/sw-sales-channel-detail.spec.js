import { createLocalVue, shallowMount } from '@vue/test-utils';
import SwSalesChannelDetail from '@administration/module/sw-sales-channel/page/sw-sales-channel-detail';
import SwSalesChannelDetailOverride from '../../../src/extension/sw-sales-channel-detail';
import SocialShoppingApiServiceMock from '../../social-shopping.api.service';
import Entity from 'src/core/data/entity.data';
import EntityCollection from 'src/core/data/entity-collection.data';
import 'src/app/component/base/sw-button';
import 'src/app/component/base/sw-button-process';

Shopware.Component.register('sw-sales-channel-detail', SwSalesChannelDetail);
Shopware.Component.override('sw-sales-channel-detail', SwSalesChannelDetailOverride);

Shopware.Defaults.SalesChannelTypeSocialShopping = '9ce0868f406d47d98cfe4b281e62f098';

const productExport = new Entity(Shopware.Utils.createId(), 'product_export', {

});

function getSalesChannelResponse(salesChannelData = {}) {
    return {
        id: Shopware.Utils.createId(),
        productExports: new EntityCollection('/product-export', 'product_export', null, null, [productExport]),
        typeId: 'foo-bar',
        extensions: null,
        ...salesChannelData
    };
}

async function createWrapper(salesChannelData) {
    const localVue = createLocalVue();
    localVue.directive('tooltip', {});
    const salesChannelResponse = getSalesChannelResponse(salesChannelData)

    return shallowMount(await Shopware.Component.build('sw-sales-channel-detail'), {
        localVue,
        mocks: {
            $tc: (path) => path,
            $route: {
                params: {
                    id: 'foo',
                },
            },
            $device: {
                getSystemKey: () => 'ALT',
            },
        },
        provide: {
            repositoryFactory: {
                create: () => ({
                    search: jest.fn(() => {
                        return Promise.resolve([]);
                    }),
                    get: jest.fn(() => {
                         return Promise.resolve(salesChannelResponse);
                    }),
                }),
            },
            exportTemplateService: {
                getProductExportTemplateRegistry: () => ({}),
            },
            socialShoppingService: new SocialShoppingApiServiceMock(),
            acl: {
                can: () => true,
            },
            feature: {
                isActive: () => true,
            },
        },
        stubs: {
            'sw-page': true,
            'router-view': true,
            'sw-sales-channel-detail-base': true,
            'sw-button': await Shopware.Component.build('sw-button'),
            'sw-button-process': await Shopware.Component.build('sw-button-process'),
            'sw-language-switch': true,
            'sw-language-info': true,
            'sw-card-view': true,
            'sw-skeleton': true,
            'sw-tabs': true,
            'sw-tabs-item': true,
            'sw-social-shopping-channel-sidebar': true,
            'sw-loader': true,
        },
    });
}

describe('src/extension/sw-sales-channel-detail', () => {
    it('should be a Vue.js component', async () => {
        const wrapper = await createWrapper();

        expect(wrapper.vm).toBeTruthy();
    });

    it('"shouldShowSidebar" won\'t throw an error if the SalesChannel is no SocialShopping SalesChannel', async () => {
        const wrapper = await createWrapper();

        expect(wrapper.vm.shouldShowSidebar).toBeFalsy();
    });

    it('onSave does an early return if the SalesChannel is no SocialShopping SalesChannel', async () => {
        const wrapper = await createWrapper();
        jest.spyOn(wrapper.vm, 'mapProductExportConfiguration');

        await wrapper.vm.onSave();

        expect(wrapper.vm.mapProductExportConfiguration).not.toHaveBeenCalled();
    });

    it('onSave maps the export configuration before actually saving the SalesChannel', async () => {
        const wrapper = await createWrapper({
            typeId: Shopware.Defaults.SalesChannelTypeSocialShopping,
            extensions: {
                socialShoppingSalesChannel: {
                    configuration: {
                        foo: 'bar',
                    },
                },
            },
        });
        jest.spyOn(wrapper.vm, 'mapProductExportConfiguration');

        await wrapper.vm.onSave();

        expect(wrapper.vm.mapProductExportConfiguration).toHaveBeenCalled();
        expect(wrapper.vm.salesChannel.productExports[0]).toHaveProperty('foo', 'bar');
    });
});
