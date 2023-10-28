import ChangesetHelper from '../helper/changeset.helper';

const changesetHelper = new ChangesetHelper();
const { Criteria } = Shopware.Data;

function setVersionId(to, from, next) {
    Shopware.State.commit('sw-publisher/setVersionId', to.params.draftVersionId);

    next();
}

Shopware.Mixin.register('sw-publisher-draft', {
    inject: ['repositoryFactory'],
    beforeRouteEnter: setVersionId,
    beforeRouteUpdate: setVersionId,
    beforeRouteLeave: setVersionId,
    computed: {
        draftVersionId() {
            return Shopware.State.get('sw-publisher').versionId;
        },
        isEntityDraft() {
            return !!this.draftVersionId;
        }
    },
    methods: {
        entityHasChanged(entity, origin) {
            return changesetHelper.hasUnsavedChanges(entity, origin);
        },
        async entityParentUpdated(entity) {
            if (!this.isEntityDraft) {
                return;
            }

            try {
                const parentEntity = await this.fetchParent(this.getEntityRepository(entity), entity.id);

                return this.compareDatesUpdated(parentEntity[0].updatedAt, this.getLatestDate(entity));
            } catch {
                return false;
            }
        },
        async fetchParent(repository, entityId) {
            const criteria = new Criteria(1, 1);
            criteria.addFilter(Criteria.equals('id', entityId));

            return await repository.search(criteria, Shopware.Context.api);
        },
        getLatestDate(entity) {
            const draft = entity.extensions.drafts[0];
            const dates = [draft.createdAt, draft.updatedAt, entity.createdAt, entity.updatedAt];

            return dates.reduce((a, b) => {
                return new Date(a) > new Date(b) ? a : b;
            });
        },
        compareDatesUpdated(parent, child) {
            if (!parent) {
                return false;
            }

            return (new Date(parent) > new Date(child));
        },
        getVersionContext(versionId = this.draftVersionId) {
            return Object.assign({}, Shopware.Context.api, {
                versionId
            });
        },
        getEntityRepository(entity) {
            return this.repositoryFactory.create(entity.getEntityName());
        },
        openDetailPage(entityId, versionId = null, routeName = this.$route.name) {
            const routerConfig = {
                name: routeName,
                params: {
                    id: entityId,
                    draftVersionId: versionId
                }
            };

            return this.$router.push(routerConfig);
        },
    }
});
