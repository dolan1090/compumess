import type { Entity } from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';
import type { LoginService } from '@administration/core/service/login.service';
import type { AxiosInstance, AxiosResponse } from 'axios';

const ApiService = Shopware.Classes.ApiService;

interface InvitePayload {
    id: string;
}

/**
 * @class
 * @extends ApiService
 * @package checkout
 */
export default class EmployeeApiService extends ApiService {
    public name = 'employeeApiService';
    EntityName = 'b2b_employee';

    constructor(httpClient: AxiosInstance, loginService: LoginService) {
        super(httpClient, loginService);
    }

    public createEmployee(employee: Entity<EntityName>, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient.post('/_action/create-employee', employee, {
            headers,
        });
    }

    public updateEmployee(employee: Entity<EntityName>, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient.patch('/_action/update-employee', employee, {
            headers,
        });
    }

    public invite(employeeId: string, additionalHeaders = {}): Promise<AxiosResponse<void>> {
        const headers = this.getBasicHeaders(additionalHeaders);
        const payload: InvitePayload = {
            id: employeeId,
        };

        return this.httpClient.post('/_action/invite-employee', payload, {
            headers,
        });
    }
}
