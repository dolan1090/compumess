import type { LoginService } from '@administration/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';
import { TRAP_KEY_1, TRAP_KEY_2 } from '../config';

const ApiService = Shopware.Classes.ApiService;

interface TagPayload {
    id: string,
    name: string,
    ruleBuilder: string,
}

/**
 * Gateway for the API end point "order/return"
 * @class
 * @extends ApiService
 * @package checkout
 */

export default class CustomerClassifyApiService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'classify') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'customerClassifyApiService';
    }

    generateTags(additionInformation: string, numberOfTags: number, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
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

        const headers = this.getBasicHeaders(additionalHeaders);

        const data = {
            additionInformation,
            numberOfTags,
            customerFields: [
                "customer_number", "order_count", "last_order_date", "order_total_amount", "last login", "first login"
            ],
            formatResponse: "{\"classifications\": [{ \"name\": [tagged_name], \"description\": [description without formula], \"ruleBuilder\": [formula]}",
        };

        return this.httpClient.post('/_action/classification-customer/generate-tags', data, {
            headers
        });
    }

    classify(groups: TagPayload[], customerIds: string[], formatResponse: string, additionalParams = {}, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        if (Shopware.License.get(TRAP_KEY_2)) {
            return this.httpClient.get(
                '_info/me',
                {
                    headers: {
                        ... this.getBasicHeaders(),
                        'sw-license-toggle': TRAP_KEY_2,
                    },
                },
            );
        }

        const headers = this.getBasicHeaders(additionalHeaders);

        const data = {
            groups,
            customerIds,
            formatResponse,
        };

        return this.httpClient.post('/_action/classification-customer/classify', data, {
            headers
        });
    }
}
