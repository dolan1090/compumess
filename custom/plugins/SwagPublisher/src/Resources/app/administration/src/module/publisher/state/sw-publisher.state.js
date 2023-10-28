Shopware.State.registerModule('sw-publisher', {
    namespaced: true,
    state: {
        versionId: null,
        draft: null,
        activity: []
    },
    mutations: {
        setVersionId(state, versionId = null) {
            state.versionId = versionId;
        },
        setDraft(state, draft = null) {
            state.draft = draft;
        },
        setActivity(state, activity)  {
            state.activity = activity;
        }
    },
    getters: {
        userActivity: (state) => {
            if (!state.activity.length) {
                return [];
            }

            let userActivity = {};

            state.activity.map(({ userId, user, createdAt, updatedAt }) => {
                const date = (updatedAt || createdAt);
                if (userActivity[userId]) {
                    if (userActivity[userId].date < date) {
                        userActivity[userId].date = date;
                    }
                } else {
                    userActivity[userId] = {
                        date,
                        user
                    };
                }
            });

            return Object.keys(userActivity).map((userId) => ({
                userId,
                ...userActivity[userId]
            }));
        }
    },
    actions: {
        resetDetailStates({ commit }) {
            commit('setVersionId', null);
            commit('setDraft', null);
            commit('setActivity', []);
        },
        async enrichActivity({ commit, state }, userRepository) {
            const userIds = [...new Set(state.activity.map(({ userId }) => userId))];
            const users = {};

            await Promise.all(userIds.map(async (userId) => {
                const { avatarMedia, firstName = '', lastName = '', userName = '' } = await userRepository.get(userId, Shopware.Context.api);

                users[userId] = {
                    avatar: avatarMedia ? avatarMedia.url : '',
                    firstName,
                    lastName,
                    userName
                };
            }));

            const enriched = state.activity.map((log) => {
                log.user = users[log.userId];

                return log;
            });

            commit('setActivity', enriched);
        }
    }
});
