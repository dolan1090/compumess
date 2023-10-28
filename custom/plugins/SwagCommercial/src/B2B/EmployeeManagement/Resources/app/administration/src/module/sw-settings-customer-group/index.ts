const { Component, License } = Shopware;

if (License.get('EMPLOYEE_MANAGEMENT-3702619')) {
    Component.override('sw-settings-customer-group-detail', () => import('./page/sw-settings-customer-group-detail'));
}
