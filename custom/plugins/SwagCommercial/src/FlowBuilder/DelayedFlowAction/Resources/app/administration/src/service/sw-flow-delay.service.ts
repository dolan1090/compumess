const ApiService = Shopware.Classes.ApiService;
import type {LoginService} from 'src/core/service/login.service';
import type {AxiosInstance, AxiosResponse} from 'axios';

/**
 * @package business-ops
 */
class SwFlowDelayService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'flow-builder') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'swFlowDelayService';
    }

    delayedExecute(ids: string[], additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const params = additionalParams;
        const headers = this.getBasicHeaders(additionalHeaders);

        if (Shopware.License.get('FLOW_BUILDER-1475275')) {
            return this.httpClient.get('api/_info/me', { headers: {
                    ...headers,
                    'sw-license-toggle': 'FLOW_BUILDER-1475275',
                }});
        }

        return this.httpClient.post(
            '/_admin/flow-builder/delayed/execute',
            { ids },
            {
                params,
                headers,
            }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

export default SwFlowDelayService;
