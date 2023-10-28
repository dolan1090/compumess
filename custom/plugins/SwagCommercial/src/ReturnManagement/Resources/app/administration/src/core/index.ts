import './app/decorator/state-styling-provider.decorator';
import OrderReturnApiService from './service/api/order-return.api.service';
import CustomerApiService from './service/api/customer.api.service';

Shopware.Service().register('orderReturnApiService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new OrderReturnApiService(initContainer.httpClient, Shopware.Service('loginService'));
});

Shopware.Service().register('customerApiService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new CustomerApiService(initContainer.httpClient, Shopware.Service('loginService'));
});
