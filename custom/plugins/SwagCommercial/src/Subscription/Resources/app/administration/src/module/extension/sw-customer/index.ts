import { componentRegister, componentOverride } from '../../../helper/license.helper';

/**
 * @package checkout
 *
 * @private
 */
if (Shopware.License.get('SUBSCRIPTIONS-1020493')) {
    componentRegister('sw-customer-detail-subscription', () => import('./component/sw-customer-detail-subscription'));

    componentOverride('sw-customer-detail', () => import('./page/sw-customer-detail'));

    componentOverride('sw-customer-detail-order', () => import('./view/sw-customer-detail-order'));
}
