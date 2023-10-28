import type {LoginService} from 'src/core/service/login.service';
import type {AxiosInstance, AxiosResponse} from 'axios';

const ApiService = Shopware.Classes.ApiService;
type RequirementPayload = {
    requirements: {
        shopwareVersion?: string,
        pluginInstalled?: Array<string>,
        appInstalled?: Array<string>
    }
};

/**
 * @package business-ops
 *
 * @class
 * @extends ApiService
 */
export default class FlowSharingService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'flow-sharing') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'flowSharingService';
    }

    downloadFlow(flowId: string): Promise<AxiosResponse<void>> {
        if (Shopware.License.get('FLOW_BUILDER-2000923')) {
            return this.httpClient.get('api/_info/me', { headers: {
                    ... this.getBasicHeaders(),
                    'sw-license-toggle': 'FLOW_BUILDER-2000923',
                }});
        }

        return this.httpClient
            .get(`/_admin/${this.getApiBasePath()}/download/${flowId}`, {
                headers: this.getBasicHeaders(),
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    checkRequirements(payload: RequirementPayload, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const params = additionalParams;
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient
            .post(`/_admin/${this.getApiBasePath()}/check-requirements`, payload, {
                params,
                headers,
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}
