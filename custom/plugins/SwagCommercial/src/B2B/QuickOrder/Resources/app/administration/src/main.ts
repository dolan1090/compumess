/**
 * @package checkout
 */

import './service';

Shopware.Component.register('swag-b2b-features-customer-specific-features',() => import('./modules/swag-b2b-features/component/swag-b2b-features-customer-specific-features'));

Shopware.Component.override('sw-customer-detail',() => import('./core/modules/sw-customer/page/sw-customer-detail'));
Shopware.Component.override('sw-customer-detail-base',() => import('./core/modules/sw-customer/view/sw-customer-detail-base'));
Shopware.Component.override('sw-bulk-edit-customer',() => import('./core/modules/sw-bulk-edit/page/sw-bulk-edit-customer'));
