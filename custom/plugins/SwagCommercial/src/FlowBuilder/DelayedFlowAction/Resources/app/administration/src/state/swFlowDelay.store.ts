const { uniqBy } = Shopware.Utils.array;

/**
 * @package business-ops
 */
export default {
    namespaced: true,

    state() {
        return {
            showWarningModal: {
                enabled: false,
                type: '',
                id: '',
                name: '',
            },
        };
    },

    mutations: {
        setShowWarningModal(state, show) {
            state.showWarningModal = show;
        },
    },
};
