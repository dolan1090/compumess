/**
 * @package system-settings
 */
import CriteriaGeneratorService from './services/criteria-generator.service';

if (Shopware.License.get('EXPORT_ASSISTANT-0490710')) {
    Shopware.Service().register('criteriaGeneratorService', () => {
        const httpClient = Shopware.Application.getContainer('init').httpClient;
        const loginService = Shopware.Service('loginService');

        return new CriteriaGeneratorService(httpClient, loginService);
    });

    Shopware.Service('searchTypeService')?.upsertType('export_assistant', {
        entityName: 'export_assistant',
        placeholderSnippet: 'swag-export-assistant.default.placeholderSearchBar',
        listingRoute: 'sw.import.export.index',
    });

    Shopware.Service('searchTypeService')?.upsertType('search_assistant', {
        entityName: 'search_assistant',
        placeholderSnippet: 'swag-search-assistant.default.placeholderSearchBar',
        listingRoute: '',
    });
}
