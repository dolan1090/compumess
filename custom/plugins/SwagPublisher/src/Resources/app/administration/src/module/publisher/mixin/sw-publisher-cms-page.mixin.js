const { Criteria } = Shopware.Data;

Shopware.Mixin.register('sw-publisher-cms-page', {
    methods: {
        formatDraftSearchResult(draftSearchResult) {
            for (let i = 0; i < draftSearchResult.length; i++) {
                let currentDraft = draftSearchResult.getAt(i);

                currentDraft.categories = [];
                currentDraft.sections = [];
                currentDraft.extensions = {};

                if (!currentDraft.translated) {
                    currentDraft.translated = {
                        name: currentDraft.name
                    };
                }

                currentDraft.cmsPage.getOrigin().previewMediaId = currentDraft.previewMediaId;
                currentDraft.cmsPage.previewMediaId = currentDraft.previewMediaId;
                currentDraft.cmsPage.previewMedia = currentDraft.previewMedia;
                currentDraft.cmsPage.versionId = currentDraft.draftVersion;

                draftSearchResult[i] = currentDraft;
            }

            return draftSearchResult;
        },
    }
});
