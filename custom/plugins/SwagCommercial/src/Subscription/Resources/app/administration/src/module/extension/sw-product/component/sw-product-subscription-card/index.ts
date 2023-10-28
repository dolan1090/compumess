import template from './sw-product-subscription-card.html.twig';
import './sw-product-subscription-card.scss';
import type { ComponentHelper } from '../../../../../type/types';

const { mapState } = Shopware.Component.getComponentHelper() as ComponentHelper;

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template,

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    computed: {
        ...mapState('swProductDetail', [
            'product',
            'parentProduct',
            'loading',
        ]),
    },
});
