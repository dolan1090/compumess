const customerCompanyTab = {
    name: 'sw.customer.detail.company',
    path: '/sw/customer/detail/:id/company',
    component: 'sw-customer-detail-company',
    meta: {
        parentPath: 'sw.customer.index',
        privilege: 'b2b_employee_management.viewer',
    },
};

function routeMiddleware(next, currentRoute) {
    if (
        currentRoute.name !== 'sw.customer.detail' ||
        currentRoute.children.some((currentRoute) => currentRoute.name === customerCompanyTab.name)
    ) {
        return;
    }

    currentRoute.children.push(customerCompanyTab);

    next(currentRoute);
}

export default function registerCustomerMiddleware() {
    Shopware.Module.register('sw-customer-company', {
        routeMiddleware,
    });
}
