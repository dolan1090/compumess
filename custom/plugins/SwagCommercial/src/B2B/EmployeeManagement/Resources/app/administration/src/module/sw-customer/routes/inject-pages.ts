const customerCompanyEmployeePages = [
    {
        name: 'sw.customer.company.employee.create',
        path: '/sw/customer/detail/:id/company/employee',
        component: 'sw-customer-employee-create',
        meta: {
            parentPath: 'sw.customer.detail.company',
            privilege: 'b2b_employee_management.creator',
        },
    },
    {
        name: 'sw.customer.company.employee.detail',
        path: '/sw/customer/detail/:id/company/employee/:employeeId',
        component: 'sw-customer-employee-detail',
        meta: {
            parentPath: 'sw.customer.detail.company',
            privilege: 'b2b_employee_management.editor',
        },
    },
];

const customerRolePages = [
    {
        name: 'sw.customer.company.role.create',
        path: '/sw/customer/detail/:id/company/role',
        component: 'sw-customer-role-create',
        meta: {
            parentPath: 'sw.customer.detail.company',
            privilege: 'b2b_employee_management.creator',
        },
    },
    {
        name: 'sw.customer.company.role.detail',
        path: '/sw/customer/detail/:id/company/role/:roleId',
        component: 'sw-customer-role-detail',
        meta: {
            parentPath: 'sw.customer.detail.company',
            privilege: 'b2b_employee_management.editor',
        },
    },
];

export default function injectPages() {
    const customerModule = Shopware.Module.getModuleByEntityName('customer');

    if (!customerModule) {
        return;
    }

    const routes = [...customerCompanyEmployeePages, ...customerRolePages];

    routes.forEach((route) => {
        customerModule.routes.set(route.name, route);
    });
}
