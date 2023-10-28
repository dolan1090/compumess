const { Component, License } = Shopware;

if (License.get('EMPLOYEE_MANAGEMENT-3702619')) {
    Component.register('sw-permission-tree', () => import('./component/sw-permission-tree'));
    Component.register('sw-customer-role-card', () => import('./component/sw-customer-role-card'));
    Component.register('sw-customer-role-create', () => import('./page/sw-customer-role-create'));
    Component.register('sw-customer-detail-company', () => import('./view/sw-customer-detail-company'));
    Component.register('sw-customer-employee-card', () => import('./component/sw-customer-employee-card'));
    Component.register('sw-customer-employee-create', () => import('./page/sw-customer-employee-create'));
    Component.extend('sw-customer-role-detail', 'sw-customer-role-create', () => import('./page/sw-customer-role-detail'));
    Component.extend('sw-customer-employee-detail', 'sw-customer-employee-create', () => import('./page/sw-customer-employee-detail'));
    Component.override('sw-customer-detail', () => import('./page/sw-customer-detail'));
}
