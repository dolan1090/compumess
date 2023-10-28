import swFlowDelayService from '../service/sw-flow-delay.service';

const { Application } = Shopware;

const initContainer = Application.getContainer('init');

Application.addServiceProvider('swFlowDelayService', (container) => {
    return new swFlowDelayService(initContainer.httpClient, container.loginService);
});
