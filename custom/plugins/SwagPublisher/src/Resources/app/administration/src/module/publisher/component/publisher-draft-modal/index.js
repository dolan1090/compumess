import template from './publisher-draft-modal.html.twig';
import './publisher-draft-modal.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default Component.wrapComponentConfig({
    template,
    inject: ['repositoryFactory', 'acl', 'draftApiService'],
    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-publisher-draft'),
        Mixin.getByName('sw-publisher-cms-page')
    ],
    props: ['page'],
    data() {
        return {
            drafts: [],
            isLoading: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            limit: 10,
            paginationPage: 1,
            term: null,
            showMediaModal: false,
            currentPage: null,
            currentDraft: null,
            showDeleteModal: false,
            listMode: 'grid',
            total: 0
        }
    },
    computed: {
        draftRepository() {
            return this.repositoryFactory.create('cms_page_draft');
        },
        pageRepository() {
            return this.repositoryFactory.create('cms_page');
        },
        modalTitle() {
            return `${this.$tc('publisher.listing.modal.title')} ${this.page.translated.name}`
        },
        sortingConCat() {
            return `${this.sortBy}:${this.sortDirection}`;
        },
        columnConfig() {
            return [{
                property: 'name',
                label: this.$tc('publisher.listing.gridName')
            }, {
                property: 'author',
                label: this.$tc('publisher.listing.gridAuthor'),
                align: 'center'
            }, {
                property: 'createdAt',
                label: this.$tc('publisher.listing.gridCreatedAt'),
                align: 'center'
            }];
        },
        sortOptions() {
            return [
                { value: 'createdAt:DESC', name: this.$tc('sw-cms.sorting.labelSortByCreatedDsc') },
                { value: 'createdAt:ASC', name: this.$tc('sw-cms.sorting.labelSortByCreatedAsc') },
                { value: 'updatedAt:DESC', name: this.$tc('sw-cms.sorting.labelSortByUpdatedDsc') },
                { value: 'updatedAt:ASC', name: this.$tc('sw-cms.sorting.labelSortByUpdatedAsc') }
            ];
        },
        draftsCriteria() {
            const criteria = new Criteria(this.paginationPage, this.limit);
            criteria
            .addAssociation('cmsPage')
            .addAssociation('previewMedia')
            .addAssociation('user')
            .addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            criteria.addFilter(Criteria.equals('pageId', this.page.id));

            if (this.term !== null) {
                criteria.setTerm(this.term);
            }

            return criteria;
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.getList();
        },
        onModalClose() {
            this.$emit('close-modal');
        },
        refreshParent() {
            this.$emit('refresh-list');
        },
        async getList() {
            try {
                this.isLoading = true;
                let searchResult = await this.draftRepository.search(this.draftsCriteria, Shopware.Context.api);

                if (!searchResult.length) {
                    this.onModalClose();
                } else {
                    searchResult = this.formatDraftSearchResult(searchResult);
                    this.total = searchResult.total;
                    this.drafts = searchResult;
                }
            } catch {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('publisher.error.action.getDrafts')
                });
            } finally {
                this.isLoading = false;
            }
        },
        async saveCmsPage(draft) {
            try {
                this.isLoading = true;
                await this.pageRepository.save(draft.cmsPage, this.getVersionContext(draft.draftVersion));
            } catch {
                this.createNotificationError({
                    message: this.$tc('publisher.error.action.saveCmsPage')
                });
            } finally {
                this.isLoading = false;
            }
        },
        async deleteDraft(draft) {
            try {
                this.isLoading = true;
                await this.draftApiService.discard(draft, this.page);
                this.resetList();
            } catch {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: this.$tc('sw-cms.components.cmsListItem.notificationDeleteErrorMessage')
                });
            } finally {
                this.isLoading = false;
                this.refreshParent();
            }
        },
        async duplicateDraft(draft) {
            try {
                this.isLoading = true;

                await this.draftApiService.duplicate(draft, this.page);
                this.refreshParent();
                this.resetList();
            } catch {
                this.createNotificationError({
                    message: this.$tc('publisher.error.action.duplicateDraft')
                });
            } finally {
                this.isLoading = false;
            }
        },
        resetList() {
            this.paginationPage = 1;
            this.drafts = [];

            this.getList();
        },
        onPageChange({ page, limit }) {
            this.paginationPage = page;
            this.limit = limit;

            this.getList();
        },
        onSearchTermChange(value) {
            this.term = value;

            this.resetList();
        },
        onSortingChange(value) {
            [this.sortBy, this.sortDirection] = value.split(':');

            this.resetList();
        },
        onListModeChange() {
            this.listMode = (this.listMode === 'grid') ? 'list' : 'grid';

            this.resetList();
        },
        onListItemClick(draft) {
            this.onModalClose();

            this.$nextTick(() => this.openDetailPage(draft.pageId, draft.draftVersion, 'sw.cms.detail'));
        },
        deleteDisabledToolTip(page) {
            return {
                showDelay: 300,
                message: this.$tc('sw-cms.general.deleteDisabledToolTip'),
                disabled: page.categories.length === 0
            };
        },
        onDuplicate(draft) {
            this.duplicateDraft(draft);
        },
        onDeleteDraft(draft) {
            this.currentDraft = draft;
            this.showDeleteModal = true;
        },
        onCloseDeleteModal() {
            this.currentDraft = null;
            this.showDeleteModal = false;
        },
        onConfirmDraftDelete() {
            this.deleteDraft(this.currentDraft);
            this.onCloseDeleteModal();
        },
        onPreviewChange(draft) {
            this.showMediaModal = true;
            this.currentDraft = draft;
        },
        onCloseMediaModal() {
            this.showMediaModal = false;
            this.currentDraft = null;
        },
        onPreviewImageChange([image]) {
            this.currentDraft.previewMediaId = image.id;
            this.currentDraft.previewMedia = image;

            this.currentDraft.cmsPage.previewMediaId = image.id;
            this.currentDraft.cmsPage.previewMedia = image;

            this.saveCmsPage(this.currentDraft);
        },
        onPreviewImageRemove(draft) {
            draft.previewMediaId = null;
            draft.previewMedia = null;

            draft.cmsPage.previewMediaId = null;
            draft.cmsPage.previewMedia = null;
            this.saveCmsPage(draft);
        }
    }
});
