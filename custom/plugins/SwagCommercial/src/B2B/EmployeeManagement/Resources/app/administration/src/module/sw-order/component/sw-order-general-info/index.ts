import template from './sw-order-general-info.html.twig';

export default {
    template,

    computed: {
        isOrderEmployee() {
            return this.order.extensions.orderEmployee.length > 0;
        },

        orderingEmployee() {
            return this.order.extensions.orderEmployee.at(0);
        },
    }
};
