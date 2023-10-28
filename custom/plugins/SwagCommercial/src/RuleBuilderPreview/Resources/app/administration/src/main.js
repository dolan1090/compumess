import './modules/rule/component/sw-rule-builder-preview';
import './modules/rule/component/sw-rule-builder-preview-indicator';
import './modules/rule/component/rule/sw-condition-tree';
import './modules/rule/component/rule/sw-condition-tree-node';
import './modules/rule/component/rule/sw-condition-type-select';
import RuleBuilderPreviewService from './core/service/api/rule-builder-preview.service';

Shopware.Service().register('ruleBuilderPreviewService', () => {
    const initContainer = Shopware.Application.getContainer('init');
    return new RuleBuilderPreviewService(initContainer.httpClient, Shopware.Service('loginService'));
});
