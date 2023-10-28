import template from './sw-cms-detail.html.twig';
import './sw-cms-detail.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default Component.wrapComponentConfig({
    template,

    inject: [
        'draftApiService',
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('sw-publisher-draft')
    ],

    data() {
        return {
            pageId: null,
            pageOrigin: null,
            page: {
                sections: []
            },
            salesChannels: [],
            isLoading: false,
            isSaveSuccessful: false,
            currentSalesChannelKey: null,
            selectedBlockSectionId: null,
            currentMappingEntity: null,
            currentMappingEntityRepo: null,
            demoEntityId: null,
            showModalUnsaved: false,
            showModalDraftName: false,
            showModalPublish: false,
            showModalParentChanges: false,
            draftName: '',
            isLoadingPreview: false,
            initialLoadDone: false
        };
    },

    watch: {
        page: {
            deep: true,
            handler() {
                if (!this.initialLoadDone) {
                    this.initialLoadDone = true;
                    this.checkForParentUpdate();
                }
            }
        }
    },

    computed: {
        pageRepository() {
            const repository = this.repositoryFactory.create('cms_page');
            repository._get = repository.get;
            repository._save = repository.save;

            repository.save = (page) => repository._save(page, this.getVersionContext());
            repository.get = (id, context, criteria) => repository._get(id, this.getVersionContext(), criteria);

            return repository;
        },

        draftVersionId() {
            return Shopware.State.get('sw-publisher').versionId;
        },

        draft() {
            return Shopware.State.get('sw-publisher').draft;
        },

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        loadPageCriteria() {
            const criteria = new Criteria(1, 1);
            const sortCriteria = Criteria.sort('position', 'ASC', true);
            const activitySortCriteria = Criteria.sort('createdAt', 'DESC');

            criteria.addAssociation('categories');

            if (this.acl.can('cms_page_activity:read')) {
                criteria.addAssociation('activities');

                criteria.getAssociation('activities')
                    .addSorting(activitySortCriteria);
            }

            if (this.isEntityDraft && this.acl.can('cms_page_draft:read')) {
                criteria.addAssociation('drafts');

                criteria.getAssociation('drafts')
                    .addFilter(Criteria.equals('draftVersion', this.draftVersionId));
            }

            criteria.getAssociation('sections')
                .addSorting(sortCriteria)
                .addAssociation('backgroundMedia')
                .getAssociation('blocks')
                .addSorting(sortCriteria)
                .addAssociation('backgroundMedia')
                .addAssociation('slots');

            return criteria;
        }
    },

    methods: {
        beforeDestroyedComponent() {
            Shopware.State.commit('cmsPageState/removeCurrentPage');
            Shopware.State.commit('cmsPageState/removeSelectedBlock');
            Shopware.State.commit('cmsPageState/removeSelectedSection');

            Shopware.State.dispatch('sw-publisher/resetDetailStates');
        },

        resolvePublisherData() {
            Shopware.State.commit('sw-publisher/setDraft', this.page.extensions.drafts[0]);
            Shopware.State.commit('sw-publisher/setActivity', this.page.extensions.activities);
            Shopware.State.dispatch('sw-publisher/enrichActivity', this.repositoryFactory.create('user'));
        },

        async resolveCmsData() {
            try {
                await this.cmsDataResolverService.resolve(this.page);
                this.updateSectionAndBlockPositions();
                Shopware.State.commit('cmsPageState/setCurrentPage', this.page);
                this.updateDataMapping();
                this.pageOrigin = Shopware.Utils.object.cloneDeep(this.page);
            } catch(exception) {
                this.createNotificationError({
                    title: exception.message,
                    message: exception.response
                });

                warn(this._name, exception.message, exception.response);
            }
        },

        async loadPage(pageId) {
            try {
                this.isLoading = true;

                this.page = await this.pageRepository.get(pageId, Shopware.Context.api, this.loadPageCriteria);

                await this.resolveCmsData();
                this.resolvePublisherData();
            } catch(exception) {
                this.createNotificationError({
                    title: exception.message,
                    message: exception.response.statusText
                });
            } finally {
                this.isLoading = false;
            }
        },

        async saveDraft() {
            let draftVersionId;

            try {
                this.isLoading = true;
                draftVersionId = await this.draftApiService.saveAsDraft(this.page, this.draftName);
                await this.openDetailPage(this.page.id, draftVersionId);

                await this.loadPage(this.page.id);
            } catch(e) {
                console.error(e);
                if (typeof draftVersionId !== 'undefined') {
                    // nth
                } else {
                    this.onError('publisher.error.action.draft');
                }
            } finally {
                this.draftName = '';
                this.isLoading = false;
            }
        },

        async saveAsNewDraftAndClose() {
            try {
                this.isLoading = true;
                await this.onSave();
                await this.draftApiService.saveAsDraft(this.page, this.draftName);

                this.closeDetailPage();
            } catch {
                this.onError('publisher.error.action.draft');
            } finally {
                this.draftName = '';
                this.isLoading = false;
            }
        },

        async saveAndPublish() {
            try {
                await this.onSave();
                this.isLoading = true;

                await this.draftApiService.merge(this.draft, this.page);
                this.getLayout(this.page.id);
            } catch {
                this.onError('publisher.error.action.saveAndPublish');
            } finally {
                this.isLoading = false;
            }
        },

        async releaseAsNew() {
            try {
                this.isLoading = true;
                const newPageId = await this.draftApiService.releaseAsNew(this.draft, this.page);
                this.getLayout(newPageId);
            } catch {
                this.onError('publisher.error.action.createNewLayout');
            } finally {
                this.isLoading = false;
            }
        },

        async updateFromParent() {
            try {
                this.isLoading = true;
                const newVersionId = await this.draftApiService.updateFromLiveVersion(this.draft, this.page);

                await this.getLayout(this.page.id, newVersionId);
                await this.loadPage(this.page.id);
            } catch {
                this.onError('publisher.error.action.updateFromParent');
            } finally {
                this.isLoading = false;
            }
        },

        createDraftName() {
            return `${this.page.name}-${this.getCurrentDate()}`
        },

        getCurrentDate() {
            const date = new Date();
            let month = '' + (date.getMonth() + 1),
                day = '' + date.getDate(),
                year = date.getFullYear();

            if (month.length < 2) {
                month = '0' + month;
            }

            if (day.length < 2) {
                day = '0' + day;
            }

            return [year, month, day].join('-');
        },

        onError(errorKey) {
            this.createNotificationError({
                title: this.$tc('publisher.error.title'),
                message: this.$tc(errorKey)
            });
        },

        onCloseClick() {
            if (!this.page.locked && this.entityHasChanged(this.page, this.pageOrigin)) {
                this.showModalUnsaved = true;
            } else {
                this.closeDetailPage();
            }
        },

        onModalSaveDraft() {
            this.showModalUnsaved = false;
            this.showModalPublish = false;

            this.$nextTick(async () => {
                await this.saveAsNewDraftAndClose();
            });
        },

        onModalSaveLayout() {
            this.showModalUnsaved = false;

            this.$nextTick(async () => {
                await this.onSave();
                this.closeDetailPage();
            });
        },

        closeDetailPage() {
            this.$router.push({
                name: 'sw.cms.index'
            });
        },

        onDiscard() {
            this.showModalUnsaved = false;
            this.showModalPublish = false;

            this.$nextTick(this.closeDetailPage);
        },

        onSaveDraft() {
            this.draftName = this.createDraftName();
            this.showModalDraftName = true;
        },

        setDraftName(draftName) {
            this.draftName = draftName;
        },

        onModalDraftNameConfirm() {
            this.showModalDraftName = false;
            this.$nextTick(this.saveDraft);
        },

        onSavePublish() {
            if (!this.page.categories.length) {
                this.saveAndPublish();
            } else {
                this.showModalPublish = true;
            }
        },

        onModalSavePublish() {
            this.showModalPublish = false;

            this.$nextTick(async () => {
                if (this.isEntityDraft) {
                    this.saveAndPublish();
                } else {
                    await this.onSave();
                }
            });
        },

        onSaveClick() {
            this.onSave();
        },

        async checkForParentUpdate() {
            this.showModalParentChanges = await this.entityParentUpdated(this.page);
        },

        onModalUpdateFromParent() {
            this.showModalParentChanges = false;
            this.$nextTick(this.updateFromParent);
        },

        onModalSaveNewLayout() {
            this.showModalParentChanges = false;
            this.$nextTick(this.releaseAsNew);
        },

        async onPreviewClick() {
            try {
                this.isLoadingPreview = true;
                const salesChannelUrl = await this.getSalesChannelUrl();
                window.open(`${salesChannelUrl}/draft/preview/${this.draft.deepLinkCode}`, '_blank');
            } finally {
                this.isLoadingPreview = false;
            }
        },

        async getSalesChannelUrl() {
            try {
                let criteria = new Criteria(1, 1);
                criteria.addAssociation('domains');
                criteria.addFilter(Criteria.multi(
                    'AND',
                    [
                        Criteria.equals('typeId', Shopware.Defaults.storefrontSalesChannelTypeId),
                        Criteria.not(
                            'AND',
                            [Criteria.equals('domains.url', null)]
                        )
                    ],
                ));

                const salesChannels = await this.salesChannelRepository.search(criteria, Shopware.Context.api);

                return salesChannels[0].domains[0].url;
            } catch {
                this.onError('publisher.error.action.getSalesChannelUrl');
            }
        },

        async getLayout(id, versionId = null) {
            await this.openDetailPage(id, versionId);
            this.loadPage(id);
        }
    }
});
