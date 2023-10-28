/**
 * @package checkout
 */
import './decorator/rule-condition-service-decoration';
import './module/sw-subscription';
import './module/sw-settings-subscription';
import './module/extension/sw-customer';
import './module/extension/sw-product';
import './module/extension/sw-order';

import type { stateStyleService } from 'src/app/service/state-style.service';
import deSnippets from './snippet/subscription.de-DE.json';
import enSnippets from './snippet/subscription.en-GB.json';

import store from './state';

import SubscriptionIntervalApiService from './service/subscription.api.service';

Shopware.Locale.extend('de-DE', deSnippets);
Shopware.Locale.extend('en-GB', enSnippets);

Shopware.State.registerModule('swCommercialSubscription', store);

Shopware.Application.addServiceProviderDecorator(
    'stateStyleDataProviderService',
    (stateStyleDataProviderService: stateStyleService) => {
        if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return stateStyleDataProviderService;

        stateStyleDataProviderService.addStyle('subscription.state', 'pending', { color: 'progress' });
        stateStyleDataProviderService.addStyle('subscription.state', 'active', { color: 'done' });
        stateStyleDataProviderService.addStyle('subscription.state', 'inactive', { color: 'neutral' });
        stateStyleDataProviderService.addStyle('subscription.state', 'cancelled', { color: 'danger' });
        stateStyleDataProviderService.addStyle('subscription.state', 'flagged_cancelled', { color: 'warning' });

        return stateStyleDataProviderService;
    },
);

Shopware.Application.addServiceProviderDecorator(
    'searchTypeService',
    (searchTypeService: any) => {
        if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return searchTypeService;

        searchTypeService.upsertType('subscription', {
            entityName: 'subscription',
            placeholderSnippet: 'commercial.subscriptions.subscriptions.listing.searchBarPlaceholder',
            listingRoute: 'sw.subscription.index',
            hideOnGlobalSearchBar: true,
        });

        return searchTypeService;
    },
);

// @ts-expect-error - service names are not typed
Shopware.Service().register('subscriptionApiService', () => {
    if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return;

    return new SubscriptionIntervalApiService(
        Shopware.Application.getContainer('init').httpClient,
        Shopware.Service('loginService'),
    );
});
