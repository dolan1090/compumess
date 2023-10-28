import type { LoginService } from '@administration/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

const ApiService = Shopware.Classes.ApiService;

/**
 * @class
 * @extends ApiService
 * @package checkout
 */
export default class SpecificFeaturesApiService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService);
        this.name = 'specificFeaturesApiService';
    }

    getSpecificFeatures(additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient.get('/_admin/licensing/features/B2B', {
            additionalParams,
            headers,
        });
    }
}
