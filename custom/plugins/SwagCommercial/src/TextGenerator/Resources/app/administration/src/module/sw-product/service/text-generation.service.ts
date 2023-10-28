/**
 * @package inventory
 */

import type { LoginService } from 'src/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

/**
 * @private
 */
export default class TextGenerationService extends Shopware.Classes.ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'textGenerationService';
    }

    generate(data: {
        keywords: string[],
        name: string,
        toneOfVoice: string,
        languageId: string,
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('TEXT_GENERATOR-2209427')) {
            return this.httpClient.post('/_action/generate-product-description', data, {
                headers: {
                    ...this.getBasicHeaders(),
                }
            });
        }
    }
}

