import ContentGenerationService from './service/content-generation.service';

Shopware.Component.override('sw-cms-el-text', () => import('./module/sw-cms/elements/text/component'));
Shopware.Component.override('sw-text-editor-toolbar', () => import('./app/component/form/sw-text-editor/sw-text-editor-toolbar'));

Shopware.Service().register('contentGenerationService', (container) => {
  const initContainer = Shopware.Application.getContainer('init');

  return new ContentGenerationService(initContainer.httpClient, Shopware.Service('loginService'));
});
