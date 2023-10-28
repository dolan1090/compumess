import template from './sw-product-category-form.html.twig';
import './sw-product-category-form.scss';

const { Component } = Shopware;

Component.override('sw-product-category-form', {
    template,

    inject: [
        'feature',
    ],

    computed: {
        dynamicAccessRulesValue: {
            get() {
                if (!this.product || !this.product.extensions) {
                    return [];
                }

                return this.product.extensions.swagDynamicAccessRules;
            },
            set(newValue) {
                if (!this.product) {
                    return;
                }

                this.product.extensions.swagDynamicAccessRules = newValue;
            },
        },

        dynamicAccessRulesInherited: {
            get() {
                if (!this.parentProduct || !this.parentProduct.extensions) {
                    return [];
                }

                return this.parentProduct.extensions.swagDynamicAccessRules;
            },
            set(newValue) {
                if (!this.parentProduct) {
                    return;
                }

                this.parentProduct.extensions.swagDynamicAccessRules = newValue;
            },
        },
    },
});
