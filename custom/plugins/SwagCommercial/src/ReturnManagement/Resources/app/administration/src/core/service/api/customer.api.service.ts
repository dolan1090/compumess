import type {AxiosInstance, AxiosResponse} from 'axios';
import type {LoginService} from '@administration/core/service/login.service';
import {TRAP_KEY_1} from '../../../config';

const ApiService = Shopware.Classes.ApiService;

/**
 * Gateway for the API end point "customer/"
 * @class
 * @extends ApiService
 * @package checkout
 */
export default class CustomerApiService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'customer') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'customerApiService';
    }

    getTurnover(customerId, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        if (Shopware.License.get(TRAP_KEY_1)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ... this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_1,
                    },
                },
            );
        }

        return this.httpClient.get(`_action/customer/${customerId}/turnover`,
            {
                headers: this.getBasicHeaders(additionalHeaders)
            })
    }
}
