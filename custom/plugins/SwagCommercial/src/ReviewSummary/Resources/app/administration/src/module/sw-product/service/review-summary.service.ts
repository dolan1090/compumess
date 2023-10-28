/**
 * @package inventory
 */

import type { LoginService } from 'src/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

/**
 * @private
 */
export default class ReviewSummaryService extends Shopware.Classes.ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'reviewSummaryService';
    }

    generate(data: {
        salesChannelId: string,
        productId: string,
        languageIds: string[],
        fake: Boolean,
        mood: string|null
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('REVIEW_SUMMARY-8147095')) {
            return this.httpClient.post('/_action/generate-review-summary', data, {
                headers: {
                    ...this.getBasicHeaders(),
                }
            });
        }
    }

    generateBulk(data: {
        minReviewCount: number
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('REVIEW_SUMMARY-8147095')) {
            return this.httpClient.post('/_action/generate-review-summary-bulk', data, {
                headers: {
                    ...this.getBasicHeaders(),
                }
            });
        }
    }
}
