import type { LoginService } from 'src/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

const ApiService = Shopware.Classes.ApiService;

/**
 * @package business-ops
 */
class PropertyExtractorService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'propertyExtractorService';
    }

    public generate(description: string, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);

        if (Shopware.License.get('PROPERTY_EXTRACTOR-9958443')) {
            return this.httpClient.get('api/_info/me', { headers: {
                ...headers,
                'sw-license-toggle': 'PROPERTY_EXTRACTOR-9958443',
            }});
        }

        return this.httpClient.post(
            '/_admin/property-extractor/extract',
            {
                description
            },
            {
                headers
            },
        ).then(response => ApiService.handleResponse(response));
    }
}

export default PropertyExtractorService;
