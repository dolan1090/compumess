import template from './swag-cms-extensions-block-behavior-config.html.twig';

const { Component } = Shopware;

Component.register('swag-cms-extensions-block-behavior-config', {
    template,

    model: {
        prop: 'block',
        event: 'block-update',
    },

    props: {
        block: {
            type: Object,
            required: true,
            default() {
                return {};
            },
        },
    },

    computed: {
        blockRuleFeatureActive() {
            return Shopware.Feature.isActive('FEATURE_SWAGCMSEXTENSIONS_8');
        },

        quickviewFeatureActive() {
            return Shopware.Feature.isActive('FEATURE_SWAGCMSEXTENSIONS_1');
        },
    }
});
