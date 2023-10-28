import type {LoginService} from 'src/core/service/login.service';
import type {AxiosInstance, AxiosResponse} from 'axios';

/**
 * @private
 * @package content
 */
export default class ContentGenerationService extends Shopware.Classes.ApiService {
    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService, null, 'application/json');
        this.name = 'contentGenerationService';
    }

    generate(data: {
        sentence: string[],
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('CONTENT_GENERATOR-7503814')) {
            return this.httpClient.get(
                'api/_info/me',
                {
                    headers: {
                        ... this.getBasicHeaders(),
                        'sw-license-toggle': 'CONTENT_GENERATOR-7503814',
                    },
                },
            );
        }

        return this.httpClient.post('/_action/cms-content/generate', data, {
            headers: {
                ...this.getBasicHeaders(),
            }
        }).catch((error: AxiosResponse) => {
            throw error;
        });
    }

    editContent(data: {
        input: string[],
        instruction: string[],
    }): Promise<AxiosResponse> {
        if (Shopware.License.get('CONTENT_GENERATOR-8940151')) {
            return this.httpClient.get(
                'api/_info/me',
                {
                    headers: {
                        ... this.getBasicHeaders(),
                        'sw-license-toggle': 'CONTENT_GENERATOR-8940151',
                    },
                },
            );
        }

        return this.httpClient.post('/_action/cms-content/edit', data, {
            headers: {
                ...this.getBasicHeaders(),
            }
        }).catch((error: AxiosResponse) => {
            throw error;
        });
    }
}
