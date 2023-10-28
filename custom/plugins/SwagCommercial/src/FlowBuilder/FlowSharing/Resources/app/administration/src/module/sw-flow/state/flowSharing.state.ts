/**
 * @package business-ops
 */
export default {
    namespaced: true,

    state: {
        flow: {},
        dataIncluded: {},
        referenceIncluded: {},
    },

    mutations: {
        setFlow(state, flow) {
            state.flow = flow;
        },

        setDataIncluded(state, dataIncluded) {
            state.dataIncluded = dataIncluded;
        },

        setReferenceIncluded(state, referenceIncluded) {
            state.referenceIncluded = referenceIncluded;
        },

        removeCurrentFlow(state) {
            state.flow = {
                eventName: '',
                sequences: [],
            };
        },

        removeReferenceIncluded(state) {
            state.referenceIncluded = {};
        },

        removeDataIncluded(state) {
            state.dataIncluded = {};
        }
    },

    actions: {
        resetFlowSharingState({ commit }) {
            commit('removeCurrentFlow');
            commit('removeReferenceIncluded');
            commit('removeDataIncluded');
        }
    }
};
