/**
 * @package checkout
 */

import type { LoginService } from 'src/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

/**
 * @private
 */

export default class CheckoutMessageService extends Shopware.Classes.ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'checkoutMessageService';
    }

    generate(data: {
        keywords: string[],
        productIds: string[],
        length: number
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('CHECKOUT_SWEETENER-3877631')) {
            return this.httpClient.post('/_action/generate-checkout-sweetener', data, {
                headers: {
                    ...this.getBasicHeaders()
                }
            });
        }
    }
}

