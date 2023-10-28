import template from './sw-product-detail-specifications.html.twig';
import './sw-product-detail-specifications.scss';

const { Component } = Shopware;

Component.override('sw-product-detail-specifications', {
    template,

    methods: {
        swagCustomizedProductsCheckInheritanceFunction() {
            return !!this.product.customFields.swagCustomizedProductsTemplateInherited;
        },

        swagCustomizedProductsRestoreInheritanceFunction() {
            this.product.customFields.swagCustomizedProductsTemplateInherited = true;
            this.product.swagCustomizedProductsTemplateId = null;
            this.$refs.swagCustomizedProductsTemplate.forceInheritanceRemove = false;

            return this.product.swagCustomizedProductsTemplateId;
        },

        swagCustomizedProductsRemoveInheritanceFunction() {
            this.product.customFields.swagCustomizedProductsTemplateInherited = false;
            this.product.swagCustomizedProductsTemplateId = null;
            this.$refs.swagCustomizedProductsTemplate.forceInheritanceRemove = true;

            return this.product.swagCustomizedProductsTemplateId;
        },
    },
});
