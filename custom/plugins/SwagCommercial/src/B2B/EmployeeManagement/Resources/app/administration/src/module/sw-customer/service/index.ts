import EmployeeApiService from './api/employee.api.service';
import RolePermissionApiService from "./api/role-permission.api.service";

const initContainer = Shopware.Application.getContainer('init');

Shopware.Service().register('employeeApiService', () => {
    return new EmployeeApiService(initContainer.httpClient, Shopware.Service('loginService'));
});

Shopware.Service().register('rolePermissionApiService', () => {
    return new RolePermissionApiService(initContainer.httpClient, Shopware.Service('loginService'));
});
