const { Component, License } = Shopware;

if (License.get('EMPLOYEE_MANAGEMENT-3702619')) {
    Component.override('sw-order-general-info', () => import('./component/sw-order-general-info'));
    Component.override('sw-order-detail', () => import('./page/sw-order-detail'));
}
