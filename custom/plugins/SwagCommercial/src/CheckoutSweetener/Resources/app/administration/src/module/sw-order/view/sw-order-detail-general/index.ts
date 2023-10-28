/*
 * @package checkout
 */

import template from './sw-order-detail-general.html.twig';
import './sw-order-detail-general.scss';

Shopware.Component.override('sw-order-detail-general', {
    template,

    computed: {
        sweetener(): string | null {
            if (!Shopware.License.get('CHECKOUT_SWEETENER-0270128')) {
                return null;
            }

            return this.order.customFields?.swagCommercialCheckoutSweetener;
        }
    }
});
