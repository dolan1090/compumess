/**
 * @package buyers-experience
 */
import PreviewSearchService from './preview-search.service';
import EntityStreamConditionService from './entity-stream-condition.service';
import { LICENSE_SERVICE, ES_MULTILINGUAL_INDEX } from '../../constants';

const { License, Feature } = Shopware;
const httpClient = Shopware.Application.getContainer('init').httpClient;
const loginService = Shopware.Service('loginService');

if (License.get(LICENSE_SERVICE) && Feature.isActive(ES_MULTILINGUAL_INDEX)) {
    Shopware.Service().register(PreviewSearchService.serviceName, () => {
        return new PreviewSearchService(httpClient, loginService);
    });

    Shopware.Service().register(EntityStreamConditionService.serviceName, () => {
        return new EntityStreamConditionService();
    });
}
