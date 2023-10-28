/**
 * @package inventory
 */
import template from './sw-product-detail-base.html.twig';

/* istanbul ignore else */
if (Shopware.License.get('MULTI_INVENTORY-3711815')) {
    Shopware.Component.override('sw-product-detail-base', {
        template,
    });
}
