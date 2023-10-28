const ROUTES = {
    SAVE_AS_DRAFT: 'draft',
    RELEASE_AS_NEW: 'releaseAsNew',
    DISCARD: 'discard',
    MERGE: 'merge',
    DUPLICATE: 'duplicate',
    UPDATE_FROM_LIVE_VERSION: 'updateFromLiveVersion'
};

export default class DraftApiService extends Shopware.Classes.ApiService {
    constructor(httpClient, loginService, apiEndpoint = '_action') {
        super(httpClient, loginService, apiEndpoint);
    }

    static get name() {
        return 'draftApiService';
    }

    saveAsDraft(entity, name) {
        const apiPath = this.getEntityApiPath(entity, ROUTES.SAVE_AS_DRAFT);
        const headers = this.getBasicHeaders({});

        return this.httpClient.post(apiPath, { name }, { headers }).then((response) => {
            return Shopware.Classes.ApiService.handleResponse(response);
        });
    }

    getEntityApiPath(entity, action) {
        return `${this.apiEndpoint}/${entity.getEntityName()}/${entity.id}/${action}`;
    }

    releaseAsNew(draft, entity) {
        return this.draftApiRequest(draft, entity, ROUTES.RELEASE_AS_NEW);
    }

    discard(draft, entity) {
        return this.draftApiRequest(draft, entity, ROUTES.DISCARD);
    }

    merge(draft, entity) {
        return this.draftApiRequest(draft, entity, ROUTES.MERGE);
    }

    duplicate(draft, entity) {
        return this.draftApiRequest(draft, entity, ROUTES.DUPLICATE);
    }

    updateFromLiveVersion(draft, entity) {
        return this.draftApiRequest(draft, entity, ROUTES.UPDATE_FROM_LIVE_VERSION);
    }

    draftApiRequest(draft, entity, action) {
        const apiPath = this.getDraftApiPath(draft, entity, action);
        const headers = this.getBasicHeaders({});

        return this.httpClient.post(apiPath, null, { headers }).then((response) => {
            return Shopware.Classes.ApiService.handleResponse(response);
        });
    }

    getDraftApiPath(draft, entity, action) {
        return `${this.apiEndpoint}/${entity.getEntityName()}/${entity.id}/${action}/${draft.draftVersion}`;
    }
}
