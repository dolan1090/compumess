import DraftService from '../service/draft.api.service';

Shopware.Application.addServiceProvider(DraftService.name, (container) => {
    const initContainer = Shopware.Application.getContainer('init');
    return new DraftService(initContainer.httpClient, container.loginService);
});
