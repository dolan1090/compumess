import type { LoginService } from '@administration/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

const ApiService = Shopware.Classes.ApiService;

/**
 * @class
 * @extends ApiService
 * @package checkout
 */
export default class RolePermissionApiService extends ApiService {
    public name = 'rolePermissionApiService';

    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService);
    }

    public getAllPermissions(additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient.get('/_action/permission', {
            headers,
        });
    }
}
