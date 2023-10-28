import template from './sw-landing-page-detail-base.html.twig';
import './sw-landing-page-detail-base.scss';

const { Component } = Shopware;

Component.override('sw-landing-page-detail-base', {
    template,

    inject: [
        'feature',
    ],

    computed: {
        dynamicAccessRulesValue: {
            get() {
                if (!this.landingPage || !this.landingPage.extensions) {
                    return [];
                }

                return this.landingPage.extensions.swagDynamicAccessRules;
            },
            set(newValue) {
                if (!this.landingPage) {
                    return;
                }

                this.landingPage.extensions.swagDynamicAccessRules = newValue;
            },
        },
    },
});
