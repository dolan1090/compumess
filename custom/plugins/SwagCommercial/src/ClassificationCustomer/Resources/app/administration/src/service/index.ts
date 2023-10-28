/**
 * @package checkout
 */

import CustomerClassifyApiService from './customer-classify.api.service';

Shopware.Service().register('customerClassifyApiService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new CustomerClassifyApiService(initContainer.httpClient, Shopware.Service('loginService'));
});
