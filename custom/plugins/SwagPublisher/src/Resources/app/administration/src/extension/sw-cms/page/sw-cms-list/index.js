import template from './sw-cms-list.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default Component.wrapComponentConfig({
    template,

    inject: ['draftApiService'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-publisher-draft'),
        Mixin.getByName('sw-publisher-cms-page')
    ],

    data() {
        return {
            pages: [],
            linkedLayouts: [],
            isLoading: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            limit: 9,
            term: null,
            currentPageType: null,
            showMediaModal: false,
            currentPage: null,
            showDeleteModal: false,
            defaultMediaFolderId: null,
            listMode: 'grid',
            assignablePageTypes: ['categories', 'products'],
            showDraftsModal: false,
            publisherBeforeSaveActionIsComplete: false,
        }
    },

    computed: {
        draftRepository() {
            return this.repositoryFactory.create('cms_page_draft');
        },

        pagesCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addAssociation('previewMedia')
                .addAssociation('sections')
                .addAssociation('categories')
                .addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            if (this.acl.can('cms_page_draft:read')) {
                criteria.addAssociation('drafts');
            }

            if (this.acl.can('cms_page_activity:read')) {
                criteria.addAssociation('activities');
            }

            if (this.term !== null) {
                criteria.setTerm(this.term);
            }

            if (this.currentPageType !== null) {
                criteria.addFilter(Criteria.equals('cms_page.type', this.currentPageType));
            }

            return criteria;
        },

        draftsCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria
                .addAssociation('cmsPage')
                .addAssociation('previewMedia')
                .addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            if (this.term !== null) {
                criteria.setTerm(this.term);
            }

            return criteria;
        }
    },

    methods: {
        async getList() {
            try {
                this.isLoading = true;
                let searchResult;

                if (this.currentPageType === 'draft') {
                    searchResult = await this.draftRepository.search(this.draftsCriteria, Shopware.Context.api);
                    searchResult = this.formatDraftSearchResult(searchResult);
                } else {
                    searchResult = await this.pageRepository.search(this.pagesCriteria, Shopware.Context.api);
                }
                this.total = searchResult.total;
                this.pages = searchResult;
            } finally {
                this.isLoading = false;
            }
        },

        async preparePublisherDuplicateCmsPage(item, behavior = { overwrites: {} }) {
            try {
                this.isLoading = true;

                const isIncompatible = await this.hasCustomForms(item);
                if (isIncompatible) {
                    this.createNotificationError({
                        message: this.$tc('publisher.listing.errors.incompatibleWithCmsMessage'),
                    });
                    throw new Error("Can't duplicate CMS page. Custom Forms are not compatible with SwagPublisher drafting.");
                }

                this.publisherBeforeSaveActionIsComplete = true;

                if (item.cmsPage) {
                    await this.draftApiService.duplicate(item, item.cmsPage);
                    this.resetList();
                } else {
                    this.onDuplicateCmsPage(item, behavior);
                }
            } catch(e) {
                throw new Error(e);
            }
        },

        onDuplicateCmsPage(item, behavior = { overwrites: {} }) {
            if (this.publisherBeforeSaveActionIsComplete) {
                this.publisherBeforeSaveActionIsComplete = false;
                this.$super('onDuplicateCmsPage', item, behavior);
            } else {
                void this.preparePublisherDuplicateCmsPage(item, behavior);
            }
        },

        async hasCustomForms(page) {
            if (!this.hasOwnProperty('getPageWithChildren')) {
                return false;
            }

            const affectedPage = page.cmsPage || page;
            const pageWithChildren = await this.getPageWithChildren(affectedPage);
            return pageWithChildren.sections.some((section) => {
                return section.blocks.some(block => block.type === 'custom-form');
            });
        },

        async saveCmsPage(item) {
            return this.$super('saveCmsPage', item.cmsPage || item, this.getVersionContext(item.draftVersion));
        },

        async deleteCmsPage(item) {
            try {
                this.isLoading = true;

                if (item.cmsPage) {
                    await this.draftApiService.discard(item, item.cmsPage);
                } else {
                    await this.pageRepository.delete(item.id, Shopware.Context.api);
                }

                this.resetList();
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-cms.components.cmsListItem.notificationDeleteErrorMessage')
                });
            } finally {
                this.isLoading = false;
            }
        },

        closeDraftModal() {
            this.showDraftsModal = false;
            this.draftsModalPage = null;
        },

        openDraftModal(page) {
            this.draftsModalPage = page;
            this.showDraftsModal = true;
        },

        onListItemClick(item) {
            const { id, draftVersion, pageId } = item;

            this.openDetailPage(pageId || id, draftVersion, 'sw.cms.detail');
        },

        onPreviewImageRemove(item) {
            item.previewMedia = null;
            item.previewMediaId = null;

            if (item.cmsPage) {
                item.cmsPage.previewMediaId = null;
                item.cmsPage.previewMedia = null;
            }

            this.saveCmsPage(item);
        },

        onPreviewImageChange([image]) {
            this.currentPage.previewMediaId = image.id;
            this.currentPage.previewMedia = image;

            if (this.currentPage.cmsPage) {
                this.currentPage.cmsPage.previewMediaId = image.id;
                this.currentPage.cmsPage.previewMedia = image;
            }

            this.saveCmsPage(this.currentPage);
        },

        getColumnConfig() {
            if (this.currentPageType !== 'draft') {
                return this.getDefaultColumnConfig();
            } else {
                return this.getDraftColumnConfig();
            }
        },

        getDraftColumnConfig() {
            return [{
                property: 'name',
                label: this.$tc('sw-cms.list.gridHeaderName'),
                inlineEdit: 'string',
                primary: true
            }, {
                property: 'createdAt',
                label: this.$tc('sw-cms.list.gridHeaderCreated')
            }];
        },

        getDefaultColumnConfig() {
            return [{
                property: 'name',
                label: this.$tc('sw-cms.list.gridHeaderName'),
                inlineEdit: 'string',
                primary: true
            }, {
                property: 'type',
                label: this.$tc('sw-cms.list.gridHeaderType')
            }, {
                property: this.feature.isActive('FEATURE_NEXT_10078')
                    ? 'assignments'
                    : 'categories.length',
                label: this.feature.isActive('FEATURE_NEXT_10078')
                    ? this.$tc('sw-cms.list.gridHeaderAssignments')
                    : this.$tc('sw-cms.list.gridHeaderAssignment'),
                sortable: false
            }, {
                property: 'createdAt',
                label: this.$tc('sw-cms.list.gridHeaderCreated')
            }];
        },

        optionContextDeleteDisabled(page) {
            return this.$super('optionContextDeleteDisabled', page) ||
                (this.currentPageType === 'draft' && !this.acl.can('cms_page_draft:delete'));
        }
    }
});
