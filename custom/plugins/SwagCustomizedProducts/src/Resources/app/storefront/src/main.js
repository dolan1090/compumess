import './polyfill/report-validity';

import SwagCustomizedProductsCollapsingValidOptions
    from './swag-customized-products-collapse/swag-customized-products-collapse.plugin';
import SwagCustomizedProductsFormValidator
    from './swag-customized-products-form-validator/swag-customized-products-form-validator.plugin';
import SwagCustomizedProductsHtmlEditor
    from './swag-customized-products-html-editor/swag-customized-products-html-editor.plugin';
import SwagCustomizedProductsStepByStepWizard
    from './swag-customized-products-step-by-step-wizard/swag-customized-products-step-by-step-wizard.plugin';
import SwagCustomizedProductsExclusionListValidation
    from './swag-customized-products-exclusion-list-validation/swag-customized-products-exclusion-list-validation';
import SwagCustomizedProductsFileUpload
    from './swag-customized-products-file-upload/swag-customized-products-file-upload.plugin';
import SwagCustomizedProductPriceDisplay
    from './swag-customized-products-price-display/swag-customized-products-price-display.plugin';
import SwagCustomizedProductsImageZoom
    from './swag-customized-products-image-zoom/swag-customized-products-image-zoom.plugin';
import SwagCustomizedProductsConfigurationShare
    from './swag-customized-products-configuration-share/swag-customized-products-configuration-share';
import SwagCustomizedProductsRestoreConfiguration
    from './swag-customized-products-restore-configuration/swag-customized-products-restore-configuration';
import SwagCustomizedProductsFieldValidation
    from './swag-customized-products-field-validation/swag-customized-products-field-validation';

window.PluginManager.register(
    'SwagCustomizedProductsFileUpload',
    SwagCustomizedProductsFileUpload,
    '[data-swag-customized-products-file-upload]',
);

window.PluginManager.register(
    'SwagCustomizedProductsCollapsingValidOptions',
    SwagCustomizedProductsCollapsingValidOptions,
    '*[data-swag-customized-products-collapse="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsFormValidator',
    SwagCustomizedProductsFormValidator,
    '[data-swag-customized-products-form-validator="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductPriceDisplay',
    SwagCustomizedProductPriceDisplay,
    '[data-swag-customized-product-price-display="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsHtmlEditor',
    SwagCustomizedProductsHtmlEditor,
    '[data-swag-customized-products-html-editor]',
);

window.PluginManager.register(
    'SwagCustomizedProductsStepByStepWizard',
    SwagCustomizedProductsStepByStepWizard,
    '*[data-swag-customized-product-step-by-step="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsExclusionListValidation',
    SwagCustomizedProductsExclusionListValidation,
    '*[data-swag-exclusion-list-validation="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsImageZoom',
    SwagCustomizedProductsImageZoom,
    '*[data-swag-customized-products-image-zoom="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsConfigurationShare',
    SwagCustomizedProductsConfigurationShare,
    '*[data-swag-customized-products-configuration-share="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsRestoreConfiguration',
    SwagCustomizedProductsRestoreConfiguration,
    '*[data-swag-customized-products-restore-configuration="true"]',
);

window.PluginManager.register(
    'SwagCustomizedProductsFieldValidation',
    SwagCustomizedProductsFieldValidation,
    '*[data-swag-customized-products-field-validation="true"]',
);

if (module.hot) {
    module.hot.accept();
}
