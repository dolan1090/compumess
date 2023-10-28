import type { AxiosInstance } from 'axios';
import type { LoginService } from '@administration/src/core/service/login.service';

const { ApiService } = Shopware.Classes;

/**
 * Gateway for the API endpoint "preview-search"
 *
 * @private
 *
 * @extends Shopware.Classes.ApiService
 *
 * @package buyers-experience
 */
export default class PreviewSearchService extends ApiService {
    static serviceName = 'previewSearchService';

    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'preview-search') {
        super(httpClient, loginService, apiEndpoint);
    }

    public async search(
        search: string,
        entity: string,
        salesChannelId: string,
        page: number,
        limit: number,
    ): Promise<unknown> {
        const headers = this.getBasicHeaders();
        const params = { search, entity, salesChannelId, p: page, limit };

        const response = await this.httpClient.get(`/_action/${this.getApiBasePath()}/search`, { params, headers });

        return ApiService.handleResponse(response);
    }
}
