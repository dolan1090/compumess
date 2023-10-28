/**
 * @package inventory
 */

import './module/sw-product/component/sw-product-basic-form';
import './module/sw-product/component/sw-product-generated-description-modal';
import './module/sw-product/component/sw-product-generated-description-ellipsis';

import TextGenerationService from './module/sw-product/service/text-generation.service';

Shopware.Service().register('textGenerationService', (container) => {
    const initContainer = Shopware.Application.getContainer('init');

    return new TextGenerationService(initContainer.httpClient, Shopware.Service('loginService'));
});
