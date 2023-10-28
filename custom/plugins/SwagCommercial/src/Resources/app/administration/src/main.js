Shopware.Component.register('sw-text-field-ai', () => import('./components/sw-text-field-ai'));

const updateLicense = () => {
    Shopware.Application.getContainer('init').httpClient.get(
        '_admin/known-ips',
        {
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${Shopware.Service('loginService').getToken}`,
                'Content-Type': 'application/json',
                'sw-license-toggle': 'FLOW_BUILDER-2909938',
            },
        },
    ).catch(() => {});
};

/* istanbul ignore next */
if (Shopware.License === undefined) {
    Object.defineProperty(Shopware, 'License', {
        get() {
            return Object.defineProperty({}, 'get', {
                get() {
                    return (flag) => {
                        return Shopware.State.get('context').app.config.licenseToggles[flag];
                    };
                },

                set() {
                    updateLicense();
                },
            });
        },

        set() {
            updateLicense();
        },
    });
}
