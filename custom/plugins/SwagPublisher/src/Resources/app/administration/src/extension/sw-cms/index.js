const acl = Shopware.Service().get('acl');
if (acl.can('cms_page_draft:read')) {
    const pageTypeService = Shopware.Service().get('cmsPageTypeService');

    pageTypeService.register({
        name: 'draft',
        icon: 'regular-file-signature',
        hideInList: true,
    });
}

Shopware.Component.override('sw-cms-block', () => import('./component/sw-cms-block'));
Shopware.Component.override('sw-cms-list-item', () => import('./component/sw-cms-list-item'));
Shopware.Component.override('sw-cms-sidebar', () => import('./component/sw-cms-sidebar'));
Shopware.Component.override('sw-cms-section', () => import('./component/sw-cms-section'));
Shopware.Component.override('sw-cms-slot', () => import('./component/sw-cms-slot'));
Shopware.Component.override('sw-cms-toolbar', () => import('./component/sw-cms-toolbar'));
Shopware.Component.override('sw-cms-list', () => import('./page/sw-cms-list'));
Shopware.Component.override('sw-cms-detail', () => import('./page/sw-cms-detail'));
