/**
 * @package checkout
 */

import './module/sw-settings/component/sw-system-config';
import './module/sw-settings-cart/component/sw-settings-cart-ai-card-description';
import './module/sw-settings-cart/component/sw-settings-cart-ai-card-preview-link';

import './module/sw-order/view/sw-order-detail-general';

import './module/sw-settings-cart/component/sw-settings-checkout-message-modal';

import CheckoutMessageService from "./module/sw-settings-cart/service/checkout-message.service";

import deSnippets from './snippet/checkout-sweetener.de-DE.json';
import enSnippets from './snippet/checkout-sweetener.en-GB.json';

Shopware.Locale.extend('de-DE', deSnippets);
Shopware.Locale.extend('en-GB', enSnippets);

Shopware.Service().register('checkoutMessageService', () => {
    const initContainer = Shopware.Application.getContainer('init');

    return new CheckoutMessageService(initContainer.httpClient, Shopware.Service('loginService'));
})
