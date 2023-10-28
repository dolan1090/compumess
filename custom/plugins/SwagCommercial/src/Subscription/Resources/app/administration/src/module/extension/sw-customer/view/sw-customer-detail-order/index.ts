import template from './sw-customer-detail-order.html.twig';
import './sw-customer-detail-order.scss';

/**
 * @package checkout
 *
 * @private
 */
export default Shopware.Component.wrapComponentConfig({
    template: template,

    methods: {
        getOrderColumns() {
            return this.$super('getOrderColumns').map((column) => {
                if (column.property === 'orderNumber') {
                    column.align = 'left';
                }

                return column;
            })
        }
    }
});
