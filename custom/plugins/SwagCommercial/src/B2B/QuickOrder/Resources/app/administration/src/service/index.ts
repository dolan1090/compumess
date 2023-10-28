import SpecificFeaturesApiService from './api/specific-features.api.service';

Shopware.Service().register('specificFeaturesApiService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new SpecificFeaturesApiService(initContainer.httpClient, Shopware.Service('loginService'));
});
