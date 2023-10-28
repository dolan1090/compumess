import type { LoginService } from 'src/core/service/login.service';
import type { AxiosInstance } from 'axios';
import { check1 } from '../helper/license.helper';
import type { TEntity, GeneratedIntervalPreview } from '../type/types';

const ApiService = Shopware.Classes.ApiService;

/**
 * @package checkout
 *
 * @public
 */
export default class SubscriptionApiService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService, apiEndpoint = 'subscription') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'subscriptionApiService';
    }

    generateIntervalPreview(limit: number, cronInterval: string, dateInterval: string): Promise<GeneratedIntervalPreview> {
        check1();

        return this.httpClient.post<GeneratedIntervalPreview>(
            `_action/${this.getApiBasePath()}/interval/generate-preview`,
            { limit, cronInterval, dateInterval },
            {  headers: this.getBasicHeaders() },
        ).then((response) => {
            return ApiService
                .handleResponse<GeneratedIntervalPreview>(response) as Promise<GeneratedIntervalPreview>;
        });
    }

    subscriptionStateTransition(subscriptionId: string, actionName: string): Promise<TEntity<'state_machine_state'>> {
        check1();

        return this.httpClient.post<TEntity<'state_machine_state'>>(
            `/_action/${this.getApiBasePath()}/${subscriptionId}/state/${actionName}`,
            {},
            {  headers: this.getBasicHeaders() },
        ).then((response) => {
            return ApiService
                .handleResponse<TEntity<'state_machine_state'>>(response) as Promise<TEntity<'state_machine_state'>>;
        });
    }
}
