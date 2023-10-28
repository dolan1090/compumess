import PropertyExtractorService from "./core/service/api/property-extractor.service";

if (Shopware.License.get('PROPERTY_EXTRACTOR-4802372')) {
    Shopware.Service().register('propertyExtractorService', () => {
        const initContainer = Shopware.Application.getContainer('init');
        return new PropertyExtractorService(initContainer.httpClient, Shopware.Service('loginService'));
    });

    Shopware.Component.override('sw-product-add-properties-modal', () => import('./module/sw-product/component/sw-product-add-properties-modal'));
    Shopware.Component.register('sw-property-assistant-modal', () => import('./module/sw-product/component/sw-property-assistant-modal'));
}
