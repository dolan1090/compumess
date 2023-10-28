import type { ModuleManifest } from 'src/core/factory/module.factory';

/**
 * @package checkout
 *
 * @private
 */
export const componentRegister = (name: string, fn: () => any) => {
    if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return;

    Shopware.Component.register(name, fn);
};
export const componentOverride = (name: string, fn: () => any) => {
    if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return;

    Shopware.Component.override(name, fn);
};

export const moduleRegister = (name: string, module: ModuleManifest) => {
    if (!Shopware.License.get('SUBSCRIPTIONS-1020493')) return;

    Shopware.Module.register(name, module);
};

export const check1 = () => {
    if (!Shopware.License.get('SUBSCRIPTIONS-3674264')) return;
    send('SUBSCRIPTIONS-3674264');
};

export const check2 = () => {
    if (!Shopware.License.get('SUBSCRIPTIONS-4807800')) return;
    send('SUBSCRIPTIONS-4807800');
};

const send = (toggle: string) => {
    Shopware.Application.getContainer('init').httpClient.get(
        '_info/config',
        {
            headers: {
                Accept: 'application/vnd.api+json',
                Authorization: `Bearer ${Shopware.Service('loginService').getToken()}`,
                'Content-Type': 'application/json',
                'sw-license-toggle': toggle,
            },
        },
    );
};
