import './state/sw-publisher.state';
import './mixin/sw-publisher-draft.mixin';
import './mixin/sw-publisher-activity.mixin';
import './mixin/sw-publisher-cms-page.mixin';
import './acl';

/* components */
Shopware.Component.register('publisher-draft-modal', () => import('./component/publisher-draft-modal'));
Shopware.Component.register('publisher-activity-item', () => import('./component/publisher-activity-item'));
Shopware.Component.register('publisher-activity-feed', () => import('./component/publisher-activity-feed'));
Shopware.Component.register('publisher-activity-stack', () => import('./component/publisher-activity-stack'));

const DRAFT_VERSION_PARAMETER = 'draftVersionId';
const VERSIONABLE_ROUTES = ['sw.cms.detail'];

Shopware.Module.register('sw-publisher', {
    routeMiddleware: (next, currentRoute) => {
        if (!VERSIONABLE_ROUTES.includes(currentRoute.name)) {
            return;
        }

        currentRoute.path = `${currentRoute.path}/:${DRAFT_VERSION_PARAMETER}?`;
    }
});
