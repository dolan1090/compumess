export default {
    namespaced: true,

    state() {
        return {
            customerPriceExist: false
        };
    },

    mutations: {
        setCustomerPriceExist(state, customerPriceExist) {
            state.customerPriceExist = customerPriceExist;
        }
    },
};
