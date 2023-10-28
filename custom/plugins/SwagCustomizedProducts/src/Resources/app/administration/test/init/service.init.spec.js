import '../../src/init/service.init';
import SwagCustomizedProductsTemplateService from '../../src/core/service/template.service';
import SwagCustomizedProductsTemplateApiService from '../../src/core/service/api/template.api.service';
import SwagCustomizedProductsTemplateOptionApiService from '../../src/core/service/api/template-option.api.service';

const serviceContainer = Shopware.Application.getContainer('service');

describe('src/init/service.init.js', () => {
    it('should contain the necessary & registered services in the container', () => {
        const registeredServices = serviceContainer.$list();

        expect(registeredServices.includes('SwagCustomizedProductsTemplateService')).toBeTruthy();
        expect(registeredServices.includes('SwagCustomizedProductsTemplateApiService')).toBeTruthy();
        expect(registeredServices.includes('SwagCustomizedProductsTemplateOptionService')).toBeTruthy();
        expect(registeredServices.includes('SwagCustomizedProductsUiLanguageContextHelper')).toBeTruthy();

        expect(
            serviceContainer.SwagCustomizedProductsTemplateService instanceof SwagCustomizedProductsTemplateService
        ).toBeTruthy();

        expect(
            serviceContainer.SwagCustomizedProductsTemplateApiService instanceof SwagCustomizedProductsTemplateApiService
        ).toBeTruthy();

        expect(
            serviceContainer.SwagCustomizedProductsTemplateOptionService instanceof SwagCustomizedProductsTemplateOptionApiService
        ).toBeTruthy();

        expect(
            typeof serviceContainer.SwagCustomizedProductsUiLanguageContextHelper
        ).toBe('function');
    });
});
