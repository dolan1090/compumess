import template from './sw-product-detail-base.html.twig';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    inject: ['acl'],
});
