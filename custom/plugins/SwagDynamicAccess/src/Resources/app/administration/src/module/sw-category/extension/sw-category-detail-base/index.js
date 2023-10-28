import template from './sw-category-detail-base.html.twig';
import './sw-category-detail-base.scss';

const { Component } = Shopware;

Component.override('sw-category-detail-base', {
    template,

    inject: [
        'feature',
    ],

    computed: {
        dynamicAccessRulesValue: {
            get() {
                if (!this.category || !this.category.extensions) {
                    return [];
                }

                return this.category.extensions.swagDynamicAccessRules;
            },
            set(newValue) {
                if (!this.category) {
                    return;
                }

                this.category.extensions.swagDynamicAccessRules = newValue;
            },
        },
    },
});
