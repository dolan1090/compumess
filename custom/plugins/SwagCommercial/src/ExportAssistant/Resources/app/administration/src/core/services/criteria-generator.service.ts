/**
 * @package system-settings
 */
import type { AxiosInstance } from 'axios';
import type CriteriaType from '@shopware-ag/admin-extension-sdk/es/data/Criteria';
import type { LoginService } from '@administration/src/core/service/login.service';

const { ApiService } = Shopware.Classes;

/**
 * Gateway for the API end point "generate-criteria"
 * @class
 * @extends Shopware.Classes.ApiService
 */
class CriteriaGeneratorService extends ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'criteriaGeneratorService';
    }

    async generate(data: {
        prompt: string,
        entity: string | null,
        criteria: CriteriaType | null,
        attempt: number | null,
        maxAttempt: number | null,
        timeout: number | null,
    }): Promise<{
        entity: string,
        criteria: CriteriaType,
    }> {
        if (Shopware.License.get('EXPORT_ASSISTANT-8419510')) {
            return this.httpClient.get(
                '_info/config',
                {
                    headers: {
                        Accept: 'application/vnd.api+json',
                        Authorization: `Bearer ${Shopware.Service('loginService')?.getToken()}`,
                        'Content-Type': 'application/json',
                        'sw-license-toggle': 'EXPORT_ASSISTANT-8419510',
                    },
                },
            );
        }

        data.attempt ??= 1;
        data.maxAttempt ??= 3;
        data.timeout ??= 25000;

        try {
            const CancelToken = this.httpClient.CancelToken;
            const source = CancelToken.source();
            const timeout = setTimeout(() => { source.cancel(); }, data.timeout);

            const response = await this.httpClient.post('/_action/generate-criteria', {
                prompt: data.prompt,
                entity: data.entity,
            }, {
                cancelToken: source.token,
                headers: this.getBasicHeaders(),
            });

            if (response.data.criteria === null) {
                const newAttempt = data.attempt + 1;

                if (newAttempt > data.maxAttempt) {
                    throw new Error('Cannot generate criteria with your request');
                }

                return await this.generate({ ...data, attempt: newAttempt });
            }

            const criteria = data.criteria ? data.criteria : new Shopware.Data.Criteria();
            criteria.filters = response.data.criteria.filters ? response.data.criteria.filters : criteria.filters;
            criteria.sortings = response.data.criteria.sortings ? response.data.criteria.sortings : criteria.sortings;

            clearTimeout(timeout);

            return {
                entity: response.data.entity,
                criteria,
            };
        } catch (error) {
            const message = error.name === 'TypeError' ? 'Something went wrong' : error.message;
            const newAttempt = data.attempt + 1;

            if (newAttempt > data.maxAttempt) {
                throw new Error(`Assistant: ${message}, please try again!`);
            }

            return this.generate({ ...data, attempt: newAttempt });
        }
    }
}

export default CriteriaGeneratorService;
