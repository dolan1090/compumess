const { Component, State } = Shopware;

Component.override('sw-product-detail', {

    computed: {
        getModeSettingSpecificationsTab() {
            const customProductsModeSettings = {
                key: 'customized_products',
                label: 'swag-customized-products-product-assignment.cardTitle',
                enabled: true,
                name: 'specifications',
            };

            return this.$super('getModeSettingSpecificationsTab').reduce((accumulator, settings) => {
                accumulator.push(settings);

                if (settings.key === 'essential_characteristics') {
                    accumulator.push(customProductsModeSettings);
                }

                return accumulator;
            }, []);
        },
    },
    beforeCreate() {
        const modeSettings = State.get('swProductDetail').modeSettings;

        State.commit('swProductDetail/setModeSettings', [
            ...modeSettings,
            'customized_products',
        ]);
    },
});
